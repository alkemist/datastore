<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\OauthService;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login()
    {
        return $this->redirectToRoute('login_google');
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout() {

    }

    #[Route(path: '/logged', name: 'logged')]
    public function logged(): Response
    {
        return $this->render('page/popup.html.twig', []);
    }
}