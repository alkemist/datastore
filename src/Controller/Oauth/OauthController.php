<?php

namespace App\Controller\Oauth;

use App\Service\OauthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OauthController extends AbstractController
{
    const ROUTE_LOGIN = 'login_google';
    const ROUTE_LOGGED = 'logged_google';

    public function __construct(
        protected OauthService $oauthService,
    ) {
    }

    public function redirectToOrigine(string $origine)
    {
        if ($origine === 'api') {
            return $this->redirectToRoute('logged');
        }

        return $this->redirectToRoute('admin');
    }
}