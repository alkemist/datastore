<?php

namespace App\Controller\Oauth;

use App\Entity\User;
use App\Security\ApiAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GoogleController extends OauthController
{
    const ROUTE_LOGIN = 'login_google';
    const ROUTE_LOGGED = 'logged_google';

    /**
     * Link to this controller to start the "connect" process
     * @throws \Exception
     */
    #[Route('/login/{project_key}/google', name: GoogleController::ROUTE_LOGIN)]
    public function loginAction(
        Request $request, ClientRegistry $clientRegistry, #[CurrentUser] ?User $user, string $project_key
    ): RedirectResponse
    {
        $callback = $request->query->get('callback');

        if ($user) {
            ApiAuthenticator::checkAuthorization($user, $project_key);

            // If already logged
            if (!$user->getCurrentAuth()->isExpired()) {
                return $this->redirectLogged($user, $project_key, $callback);
            }
        }

        $redirectUri = $this->generateUrl(
            self::ROUTE_LOGGED, [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        if ($callback) {
            $callback = ApiAuthenticator::buildCallback($callback, $project_key);
        }

        // will redirect to Google!
        return $clientRegistry
            ->getClient('google_main') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'email', 'profile' // the scopes you want to access
            ], [
                'prompt' => 'consent', // Force to show consent & regenerate refresh token
                'access_type' => 'offline',
                'state' => $callback,
                'redirect_uri' => $redirectUri
            ]);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     * @throws \Exception
     */
    #[Route('/logged/google', name: GoogleController::ROUTE_LOGGED)]
    public function loggedAction(#[CurrentUser] User $user,
                                 Request             $request,
    ): RedirectResponse
    {
        $state = $request->query->get('state');
        $apiProject = ApiAuthenticator::getProjectByRequest($request);

        return $this->redirectLogged(
            $user,
            $apiProject,
            preg_match('/^(\w+)$/i', $state)
                ? null
                : $state
        );
    }
}