<?php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Entity\User;
use App\Model\ApiResponse;
use App\Service\OauthService;
use Doctrine\ORM\NonUniqueResultException;
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

class ApiAuthenticator extends AbstractAuthenticator
{
    public const TOKEN_HEADER_KEY = 'X-AUTH-TOKEN';
    public const PROJECT_HEADER_KEY = 'X-AUTH-PROJECT';
    public const PROJECT_QUERY_KEY = 'project_key';

    private OauthService $oauthService;

    public function __construct(
        OauthService                           $oauthService,
        private readonly TokenStorageInterface $tokenStorage
    )
    {
        $this->oauthService = $oauthService;
    }

    static function buildCallback($callback, $projectKey, $code = ''): string
    {
        $callback .= "?" . self::PROJECT_QUERY_KEY . "=$projectKey";

        if ($code) $callback .= "&code=$code";

        return $callback;
    }

    public static function getProjectByRequest(Request $request): string
    {
        $state = $request->query->get('state');
        preg_match('@https?://(.*)/authorize/?(\w+)\?' . self::PROJECT_QUERY_KEY . '=([\w\-]+)@i', $state, $m);
        return count($m) === 4 ? $m[3] : '';
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

    /**
     * @throws NonUniqueResultException
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::TOKEN_HEADER_KEY);
        $apiProject = $request->headers->get(self::PROJECT_HEADER_KEY);

        if (!$apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided', [], 200);
        }

        if (!$apiProject) {
            throw new CustomUserMessageAuthenticationException('No API project provided', [], 200);
        }

        $user = $this->oauthService->getUserByToken($apiToken);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException(
                "Expired token",
                [],
                200
            );
        }

        self::checkAuthorization($user, $apiProject);

        try {
            $this->oauthService->refreshUser($user);
        } catch (Exception $exception) {
            throw new CustomUserMessageAuthenticationException(
                $exception->getMessage(),
                [],
                200
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

    static function checkAuthorization(User $user, string|null $apiProject): void
    {
        if (!$apiProject && !$user->isAdmin()) {
            throw new CustomUserMessageAuthenticationException(
                "Empty project",
                [],
                403
            );
        }

        if (!$user->hasProjectAuthorization($apiProject) && !$user->isAdmin()) {
            throw new CustomUserMessageAuthenticationException(
                "Unauthorized project",
                [],
                403
            );
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->tokenStorage->setToken(null);

        if ($request->attributes->get('_route') === 'api_profile' && $exception->getCode() !== 403) {
            return (new ApiResponse())
                ->setResponse(
                    strtr($exception->getMessageKey(), $exception->getMessageData())
                )
                ->setItem(null)
                ->toJson();
        }

        return (new ApiResponse())
            ->isUnauthorized(
                strtr($exception->getMessageKey(), $exception->getMessageData()),
                $exception->getCode()
            )
            ->toJson();
    }
}