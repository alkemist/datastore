<?php

namespace App\Controller;

use App\Controller\Oauth\OauthController;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends OauthController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request, Security $security
    ): RedirectResponse|Response {
        $callback = $request->query->get('callback');
        /** @var User|null $user */
        $user = $security->getUser();


        if ($user && !$user->isExpired()) {
            return $this->redirectLogged($user, $callback);
        }

        return $this->render('page/login.html.twig', [
            'googleCallback'   => $callback,
            'webauthnCallback' => $this->generateUrl('logged', [
                'callback' => $callback
            ],                                       UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    #[Route('/logged', name: 'logged')]
    public function logged(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        return $this->redirectLogged($user, $request->query->get('callback'));
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout()
    {

    }
}