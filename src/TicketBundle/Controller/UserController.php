<?php

namespace TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TicketBundle\Entity\TicketUser;
use TicketBundle\Form\TicketUserType;

class UserController extends Controller
{
    public function getUsersAction(Request $request){
        $auth = $this->get('app.auth')->checkLogin($request);
        if ($auth) {
            $form = $this->createForm(new TicketUserType($this->container), $user = new TicketUser());
            $form->remove('name');
            $form->remove('email');
            $form->remove('password');
            $form->remove('valid');
            $param = array();
            $data = $request->request->all();
            if (isset($data['search'])) {
                $param['name'] = $data['search'];
                $search = $data['search'];
            } else
                $search = "";
            $query = $em = $this->getDoctrine()->getManager()->getRepository("TicketBundle:TicketUser")->getUsers($param);
            $paginator = $this->get('knp_paginator');
            $users = $paginator->paginate($query, $request->query->getInt('page', 1), 5);
            return $this->render('TicketBundle:User:index.html.twig', array(
                'users' => $users, 'search' => $search, 'form' => $form->createView()));
        } else {
            $this->addFlash('danger', 'You don´t have permission');
            return $this->redirectToRoute('index');
        }
    }

    public function validateUserAction(TicketUser $user, Request $request){
        $auth = $this->get('app.auth')->checkLogin($request);
        $valid = $this->get('app.auth')->checkUserValid($request);
        if ($auth && $valid) {
            $session = $request->getSession();
            $role = $session->get('role');
            if ($role == 'admin') {
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
        } else {
            $this->addFlash('danger', 'You don´t have permission');
            return $this->redirectToRoute('index');
        }
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
                $this->get('app.auth')->login($request);
                $this->addFlash('notice', 'User created success');
            }
            return $this->redirectToRoute('users');
        }
        return $this->render('TicketBundle:User:new.html.twig', array(
                    'form' => $form->createView()));
    }
}
