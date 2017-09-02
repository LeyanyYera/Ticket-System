<?php

namespace TicketBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TicketBundle\Entity\Status;
use TicketBundle\Entity\Ticket;
use TicketBundle\Entity\TicketUser;
use TicketBundle\Form\TicketType;
use TicketBundle\Form\TicketUserType;

class UserController extends Controller
{
    public function indexAction(){
        $form = $this->createForm(new TicketUserType($this->container), $user = new TicketUser());
        $form->remove('valid');
        $form->remove('name');
        $form->remove('email');
        $form->remove('password');
        return $this->render('TicketBundle:Default:index.html.twig', array(
           'form'=>$form->createView()));
    }

    public function loginAction(Request $request){
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('TicketBundle:TicketUser')->findBy(array('email'=>$data['email']));
        if(!empty($user)){
            if(password_verify($data['password'], $user[0]->getPassword())){
                $session = $request->getSession();
                $session->set('id', $user[0]->getId());
                $session->set('name', $user[0]->getName());
                $session->set('role', $user[0]->getRole());
                $session->set('email', $user[0]->getEmail());
                return $this->redirect($this->generateUrl('ticket'));
            }
            $this->addFlash('danger', 'Wrong password, please try again');
            return $this->indexAction();
        }
        $this->addFlash('danger', 'User does not exist, please verify');
        return $this->indexAction();
    }

    public function getUsersAction(Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)) {
                $form = $this->createForm(new TicketUserType($this->container), $user = new TicketUser());
                $form->remove('name');
                $form->remove('email');
                $form->remove('password');
                $form->remove('valid');
                $param = array();
                $data = $request->request->all();
                if(isset($data['search'])){
                    $param['name'] = $data['search'];
                    $search = $data['search'];
                }
                else
                    $search = "";
                $users = $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser")->getUsers($param);
                return $this->render('TicketBundle:User:index.html.twig', array(
                    'users' => $users, 'search'=>$search, 'form'=>$form->createView()));
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function validateUserAction(TicketUser $user, Request $request){
        if($this->checkLogin($request)){
            if($this->checkUserValid($request)) {
                $session = $request->getSession();
                $role = $session->get('role');
                if($role == 'admin'){
                    $em = $this->getDoctrine()->getManager();
                    $user->setValid(true);
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('notice', 'User validated success');
                    return $this->redirectToRoute('users');
                }
                else{
                    $this->addFlash('danger', "You don't have permission to this action");
                    return $this->redirectToRoute('users');
                }
            }
            else{
                $this->addFlash('danger', 'Invalid user, please contact the admin');
                return $this->redirectToRoute('index');
            }
        }
        $this->addFlash('danger', 'You must be logged');
        return $this->redirectToRoute('index');
    }

    public function newUserAction(Request $request){
        $form = $this->createForm(new TicketUserType($this->container), $user = new TicketUser());
        $form->remove('valid');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $request->request->all();
            $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser");
            $password = password_hash($data['ticket_user']['password'], PASSWORD_DEFAULT);
            $user = $em->findBy(array('email'=>$data['ticket_user']['email']));
            if (is_array($user) && empty($user)) {
                $em = $this->getDoctrine()->getManager();
                $user = new TicketUser();
                $user->setName($data['ticket_user']['name']);
                $user->setEmail($data['ticket_user']['email']);
                $user->setPassword($password);
                $user->setValid(false);
                $user->setRole('default');
                $em->persist($user);
                $em->flush();
                $this->addFlash('notice', 'Ticket created success');
            }
            return $this->getUsersAction($request);
        }
        return $this->render('TicketBundle:User:new.html.twig', array(
                    'form' => $form->createView()));
    }

    public function logoutAction(Request $request){
        $session = $request->getSession();
        $session->clear();
        return $this->redirect($this->generateUrl('index'));
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
}
