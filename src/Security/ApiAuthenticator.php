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
    private const TOKEN_HEADER_KEY = 'X-AUTH-TOKEN';
    private const PROJECT_HEADER_KEY = 'X-AUTH-PROJECT';

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
        return !str_starts_with($request->attributes->get('_route'), 'api_public_');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::TOKEN_HEADER_KEY);
        $apiProject = $request->headers->get(self::PROJECT_HEADER_KEY);

        if (!$apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        if (!$apiProject) {
            throw new CustomUserMessageAuthenticationException('No API project provided');
        }

        $user = $this->oauthService->getUserByToken($apiProject, $apiToken);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException("Expired token or unauthorized project");
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
        if ($request->attributes->get('_route') === 'api_profile') {
            return (new ApiResponse())
                ->setResponse(strtr($exception->getMessageKey(), $exception->getMessageData()))
                ->setItem(null)
                ->toJson();
        }

        return (new ApiResponse())
            ->isUnauthorized(
                strtr($exception->getMessageKey(), $exception->getMessageData())
            )
            ->toJson();
    }
}