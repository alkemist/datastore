<?php

namespace App\Controller;

use App\Controller\Oauth\OauthController;
use App\Entity\User;
use App\Security\ApiAuthenticator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends OauthController
{
    /**
     * @throws \Exception
     */
    #[Route('/login', name: 'login')]
    public function login(Request $request, Security $security
    ): RedirectResponse|Response
    {
        return $this->projectLogin($request, $security, 'admin');
    }

    /**
     * @throws \Exception
     */
    #[Route('/login/{project_key}', name: 'login_project')]
    public function projectLogin(Request $request, Security $security, string $project_key
    ): RedirectResponse|Response
    {
        $callback = $request->query->get('callback');
        /** @var User|null $user */
        $user = $security->getUser();

        if ($user) {
            ApiAuthenticator::checkAuthorization($user, $project_key);

            if (!$user->getCurrentAuth()->isExpired()) {
                return $this->redirectLogged($user, $project_key, $callback);
            }
        }

        return $this->render('page/login.html.twig', [
            'googleCallback' => $callback,
            'projectKey' => $project_key,
            'webauthnCallback' => $this->generateUrl(
                'project_logged', [
                'callback' => $callback,
                'project_key' => $project_key,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/logged/{project_key}', name: 'project_logged')]
    public function project_logged(Request $request, #[CurrentUser] User $user, string $project_key): RedirectResponse
    {
        return $this->redirectLogged($user, $project_key, $request->query->get('callback'));
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout()
    {

    }
}