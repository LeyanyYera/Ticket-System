<?php

namespace TicketBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class Auth
{

    private $manager;
    private $router;

    public function __construct($manager, RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    public function checkLogin(Request $request)
    {
        $session = $request->getSession();
        if ($session->has('id'))
            return true;
        return false;
    }

    public function checkUserValid(Request $request)
    {
        $session = $request->getSession();
        $user = $this->manager->getRepository('TicketBundle:TicketUser')->findBy(array('email' => $session->get('email')));
        if ($user[0]->getValid() != null) {
            return $user[0]->getValid();
        } else
            return false;
    }

    public function login(Request $request)
    {
        $data = $request->request->all();
        if (isset($data['ticket_user']['email']) && $data['ticket_user']['email'] != null &&
            isset($data['ticket_user']['password']) && $data['ticket_user']['password'] != null
        ) {
            $user = $this->manager->getRepository('TicketBundle:TicketUser')->findBy(array('email' => $data['ticket_user']['email']));
            if (!empty($user)) {
                if (password_verify($data['ticket_user']['password'], $user[0]->getPassword())) {
                    $session = $request->getSession();
                    $session->set('id', $user[0]->getId());
                    $session->set('name', $user[0]->getName());
                    $session->set('role', $user[0]->getRole());
                    $session->set('email', $user[0]->getEmail());
//                    return new RedirectResponse($this->router->generate('ticket'));
                } else {
                    $this->addFlash('danger', 'Wrong password, please try again');
                    return new RedirectResponse($this->router->generate('index'));
                }
            } else {
                $this->addFlash('danger', 'User does not exist, please verify');
                return new RedirectResponse($this->router->generate('index'));
            }
        } else {
            $this->addFlash('danger', 'Must be completed email and password');
            return new RedirectResponse($this->router->generate('index'));
        }
    }
}