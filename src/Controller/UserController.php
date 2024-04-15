<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login()
    {
        return $this->render('page/login.html.twig', [

        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout()
    {

    }

    #[Route(path: '/logged', name: 'logged')]
    public function logged(): Response
    {
        return $this->render('page/popup.html.twig', []);
    }
}