<?php

namespace App\Controller\Oauth;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GoogleController extends OauthController
{
    const ROUTE_LOGIN = 'login_google';
    const ROUTE_LOGGED = 'logged_google';

    /**
     * Link to this controller to start the "connect" process
     */
    #[Route('/login/google/{origine}', name: GoogleController::ROUTE_LOGIN, requirements: ['origine' => '\w+'])]
    public function loginAction(
        Request $request, ClientRegistry $clientRegistry, #[CurrentUser] ?User $user, string $origine = ''
    ) {
        // If already logged
        if ($user && !$user->isExpired()) {
            return $this->redirectToOrigine($origine);
        }

        // will redirect to Google!
        return $clientRegistry
            ->getClient('google_main') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                           'email', 'profile' // the scopes you want to access
                       ], [
                           'prompt'      => 'consent', // Force to show consent & regenerate refresh token
                           'access_type' => 'offline',
                           'state'       => $origine
                       ]);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     */
    #[Route('/logged/google', name: GoogleController::ROUTE_LOGGED)]
    public function loggedAction(Request $request, ClientRegistry $clientRegistry)
    {
        return $this->redirectToOrigine($request->query->get('state'));
    }
}