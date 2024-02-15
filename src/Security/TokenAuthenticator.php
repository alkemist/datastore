<?php

namespace App\Security;

use App\Controller\ApiController;
use App\Entity\User;
use App\Model\ApiResponse;
use App\Service\OauthService;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private OauthService          $oauthService,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === ApiController::ROUTE_TOKEN;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('User not logged');
        }

        /** @var User $user */
        $user = $token->getUser();

        try {
            $this->oauthService->refreshUser($user);
        } catch (Exception $exception) {
            throw new CustomUserMessageAuthenticationException(
                $exception->getMessage()
            );
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $user->getEmail(),
                function () use ($user) {
                    return $user;
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return (new ApiResponse())
            ->isUnauthorized(
                strtr($exception->getMessageKey(), $exception->getMessageData())
            )
            ->toJson();
    }
}