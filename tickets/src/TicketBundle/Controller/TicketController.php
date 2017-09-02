<?php

namespace TicketBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TicketBundle\Entity\Status;
use TicketBundle\Entity\Ticket;
use TicketBundle\Entity\TicketUser;
use TicketBundle\Form\TicketType;

class TicketController extends Controller
{
    public function getTicketsAction(Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)){
                $session = $request->getSession();
                $role = $session->get('role');
                $form = $this->createForm(new TicketType($this->container), $ticket = new Ticket());
                $form->remove('assignee');
                $form->remove('title');
                $form->remove('body');
                $form->remove('status');
                $param = array();
                $data = $request->request->all();
                if(isset($data['search']))
                    $param['title'] = $data['search'];
                if($role === 'default')//if user is 'default' it shows their tickets assigned, otherwise all are shown
                    $param['assignee'] = $session->get('id');
                if(isset($data['selected'])){
                    $param['status'] = $data['selected'];
                    $selected = $data['selected'];
                }
                else{
                    $selected = 1;
                    $param['status'] = 1;
                }
                if(isset($data['search']))
                    $search = $data['search'];
                else
                    $search = "";
                $tickets = $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:Ticket")->getTickets($param);
                $status = $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:Status")->findAll();
                return $this->render('TicketBundle:Ticket:index.html.twig', array(
                    'tickets' => $tickets, 'status'=>$status, 'selected'=>$selected, 'search'=>$search, 'form'=>$form->createView()));
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        else{
            $this->addFlash('danger', 'You must be logged');
            return $this->redirectToRoute('index');
        }
    }

    public function newTicketAction(Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)){
                $form = $this->createForm(new TicketType($this->container), $ticket = new Ticket());
                $form->remove('status');
                $form->remove('assignee');
                $em = $this->getDoctrine()->getManager();
                $session = $request->getSession();
                $email = $session->get('email');
                $user_logged = $em->getRepository("TicketBundle:TicketUser")->findBy(array('email'=>$email));
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $request->request->all();
                    $ticket = $em->getRepository("TicketBundle:Ticket")->findBy(array('title'=>$data['ticket']['title'], 'body'=>$data['ticket']['body'], 'author'=>$user_logged[0]));
                    if (is_array($ticket) && empty($ticket)) {
                        $date = new \DateTime(date("Y-m-d H:i:s"));
                        $assignee = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser")->find($data['assignee_id']);
                        $status_selected = $this->getDoctrine()->getManager()->getRepository("TicketBundle:Status")->find($data['status_id']);
                        $this->addTicket($data, $user_logged, $status_selected, $date, $assignee);
                        $this->addFlash('notice', 'Ticket created success');
                    }
                    return $this->getTicketsAction($request);
                }
                $status = $this->getDoctrine()->getManager()->getRepository("TicketBundle:Status")->findAll();
                $users = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser")->findAll();
                return $this->render('TicketBundle:Ticket:new.html.twig', array(
                    'form' => $form->createView(),
                    'assignee_default'=>$user_logged[0]->getEmail(),
                    'status_default'=>1,
                    'status'=>$status,
                    'assignee'=>$users));
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function editTicketAction(Request $request, Ticket $ticket){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)){
                $em = $this->getDoctrine()->getManager();
                $form = $this->createForm(new TicketType($this->container), $ticket);
                $form->remove('assignee');
                $form->remove('status');
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $_ticket = $em->getRepository('TicketBundle:Ticket')->checkTicket($ticket->getTitle(), $ticket->getBody(), $ticket->getAuthor()->getName());
                    if(empty($_ticket)){
                        $em->persist($ticket);
                        $em->flush();
                        $this->addFlash('notice', 'Ticket edited success');
                        return $this->redirectToRoute('ticket');
                    }
                }
                return $this->render('TicketBundle:Ticket:edit.html.twig', array(
                    'ticket' => $ticket,
                    'id' => $ticket->getId(),
                    'form' => $form->createView()
                ));
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function deleteTicketAction(Ticket $ticket, Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)){
                $em = $this->getDoctrine()->getManager();
                $em->remove($ticket);
                $em->flush();
                $this->addFlash('notice', 'Ticket deleted success');
                return $this->redirectToRoute('ticket');
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function closeTicketAction(Ticket $ticket, Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)) {
                $em = $this->getDoctrine()->getManager();
                $status = $em->getRepository('TicketBundle:Status')->find(2);
                $ticket->setStatus($status);
                $em->persist($ticket);
                $em->flush();
                $this->addFlash('notice', 'Ticket closed success');
                return $this->redirectToRoute('ticket');
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function assignTicketAction(Ticket $ticket, Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)) {
                $data = $request->request->all();
                $form = $this->createForm(new TicketType($this->container), $ticket);
                $form->remove('title');
                $form->remove('body');
                $form->remove('status');
                $form->remove('assignee');
                $form->handleRequest($request);
                $search = '';
                if(isset($data['user']))
                    $search = $data['user'];
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $assignee = $em->getRepository('TicketBundle:TicketUser')->find($data['user']);
                    $ticket->setAssignee($assignee);
                    $em->persist($ticket);
                    $em->flush();
                    $this->addFlash('notice', 'Assignee changed success');
                    return $this->redirectToRoute('ticket');
                }
                $param = array();
                $param['valid'] = true;
                if(isset($data['search']) && !empty($data['search']))
                    $param['name'] = $data['search'];

                $users = $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser")->getUsers($param);

                return $this->render('TicketBundle:Ticket:assign.html.twig', array(
                    'ticket' => $ticket,
                    'id' => $ticket->getId(),
                    'users'=>$users,
                    'search'=>$search,
                    'form' => $form->createView()
                ));
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function checkLogin(Request $request){
        $session = $request->getSession();
        if($session->has('id'))
            return true;
        return false;
    }

    public function checkUserValid(Request $request){
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('TicketBundle:TicketUser')->findBy(array('email'=>$session->get('email')));
        return $user[0]->getValid();
    }

    /**
     * @param $data
     * @param $user_logged
     * @param $status_selected
     * @param $date
     * @param $assignee
     * @param $em
     */
    public function addTicket($data, $user_logged, $status_selected, $date, $assignee)
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = new Ticket();
        $ticket->setTitle($data['ticket']['title']);
        $ticket->setBody($data['ticket']['body']);
        $ticket->setAuthor($user_logged[0]);
        $ticket->setStatus($status_selected);
        $ticket->setCreated($date);
        $ticket->setAssignee($assignee);
        $em->persist($ticket);
        $em->flush();
    }
}
