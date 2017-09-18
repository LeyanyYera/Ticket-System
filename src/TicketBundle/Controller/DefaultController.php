<?php

namespace TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TicketBundle\Form\TicketUserType;
use TicketBundle\Entity\TicketUser;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $form = $this->createForm(new TicketUserType($this->container), $user = new TicketUser());
        $form->remove('valid');
        $form->remove('name');
        return $this->render('TicketBundle:Default:index.html.twig', array(
            'form' => $form->createView()));
    }

    public function loginAction(Request $request)
    {
        $this->get('app.auth')->login($request);
        return $this->redirect($this->generateUrl('ticket'));
    }

    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->clear();
        return $this->redirect($this->generateUrl('index'));
    }

}
