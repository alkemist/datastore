<?php

namespace App\Controller\Oauth;

use App\Entity\User;
use App\Service\OauthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OauthController extends AbstractController
{
    const ROUTE_LOGIN = 'login_google';
    const ROUTE_LOGGED = 'logged_google';

    public function __construct(
        protected OauthService $oauthService,
    ) {
    }

    public function redirectLogged(User $user, string $callback): RedirectResponse
    {
        return $this->redirect($callback . '?code=' . $user->getGoogleRefreshToken());
    }
}