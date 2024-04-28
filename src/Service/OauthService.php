<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpKernel\KernelInterface;

class OauthService
{
    public function __construct(
        private KernelInterface        $kernel,
        private ClientRegistry         $clientRegistry,
        private EntityManagerInterface $entityManager,
        private UserRepository         $userRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    function registerUser(OAuth2ClientInterface $client, AccessToken $accessToken): User
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);

        $email = $googleUser->getEmail();
        $refreshToken = $accessToken->getRefreshToken();
        $expires = $accessToken->getExpires();

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $users = $this->userRepository->findAll();

            if (count($users) > 0) {
                throw new Exception("User '$email' not exist");
            }

            if ($this->kernel->getEnvironment() !== 'dev') {
                throw new Exception("Application not initialized");
            }

            $user = new User();
            $user->setEmail($googleUser->getEmail());
            $user->setUsername($googleUser->getName());
            $user->setRoles(['ROLE_ADMIN']);
        }

        $user->updateToken();
        $user->setGoogleId($googleUser->getId());
        $user->setGoogleRefreshToken($refreshToken);
        $user->setGoogleExpires($expires);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    function refreshUser(User $user)
    {
        if ($user->isExpired()) {
            $client = $this->clientRegistry->getClient('google_main');
            $accessToken = $client->refreshAccessToken($user->getGoogleRefreshToken() ?? '');

            $user->updateToken();
            $user->setGoogleRefreshToken($accessToken->getToken());
            $user->setGoogleExpires($accessToken->getExpires());
            $this->entityManager->flush();
        }

        return $user;
    }

    public function clear(User $user): void
    {
        $user->setGoogleRefreshToken(null);
        $user->setGoogleExpires(null);
        $user->setTokenExpires(null);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getUserByToken($apiToken): ?User
    {
        return $this->userRepository->findOneByToken($apiToken);
    }
}