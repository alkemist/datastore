<?php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Model\ApiResponse;
use App\Service\OauthService;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticator extends AbstractAuthenticator
{
    private OauthService $oauthService;

    public function __construct(
        OauthService $oauthService,
    ) {
        $this->oauthService = $oauthService;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->attributes->get('_route'), 'api_');
    }

    public function authenticate(Request $request): Passport
    {
        //$apiToken = $request->headers->get('X-AUTH-TOKEN');
        $apiToken = $request->query->get('token'); // For dev


        if (!$apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $user = $this->oauthService->getUserByToken($apiToken);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException("Expired token");
        }

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