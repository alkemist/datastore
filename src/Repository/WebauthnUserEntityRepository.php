<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

final class WebauthnUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    /**
     * The UserRepository $userRepository is the repository
     * that already exists in the application
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository
    ) {
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
                                                     'username' => $username,
                                                 ]);

        return $this->getUserEntity($user);
    }

    /**
     * Converts a Symfony User (if any) into a Webauthn User Entity
     * @throws InvalidDataException
     */
    public function getUserEntity(null|User $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        $user->updateToken();
        $this->entityManager->flush();

        return new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $user->getEmail(),
            $user->getUsername(),
            null
        );
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
                                                     'email' => $userHandle,
                                                 ]);

        return $this->getUserEntity($user);
    }
}
