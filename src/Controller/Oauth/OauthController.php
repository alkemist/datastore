<?php

namespace App\Controller\Oauth;

use App\Entity\User;
use App\Security\ApiAuthenticator;
use App\Service\OauthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OauthController extends AbstractController
{
    public function __construct(
        protected OauthService $oauthService,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function redirectLogged(User $user, ?string $projectKey, ?string $callback): RedirectResponse
    {
        if (!$projectKey || !$callback) {
            $callback = $user->isAdmin()
                ? $this->generateUrl('admin', [], UrlGeneratorInterface::ABSOLUTE_URL)
                : $this->generateUrl('index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $callback = ApiAuthenticator::buildCallback(
                $callback,
                $projectKey,
                $user->getCurrentAuth()
                    ->getToken()
            );
        }
        
        return $this->redirect($callback);
    }
}