<?php

namespace App\Controller\Oauth;

use App\Entity\User;
use App\Service\OauthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OauthController extends AbstractController
{
    public function __construct(
        protected OauthService $oauthService,
    ) {
    }

    public function redirectLogged(User $user, ?string $callback): RedirectResponse
    {
        if (!$callback) {
            $callback = $user->isAdmin()
                ? $this->generateUrl('admin', [], UrlGeneratorInterface::ABSOLUTE_URL)
                : $this->generateUrl('index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->redirect($callback . '?code=' . $user->getToken());
    }
}