<?php

declare(strict_types=1);

namespace App\Guesser;

use App\Repository\WebauthnUserEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\Bundle\Security\Guesser\UserEntityGuesser;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

final class FromQueryParameterIdGuesser implements UserEntityGuesser
{
    public function __construct(
        private readonly WebauthnUserEntityRepository $userEntityRepository
    ) {
    }

    /**
     * @throws InvalidDataException
     */
    public function findUserEntity(Request $request): PublicKeyCredentialUserEntity
    {
        $userHandle = $request->attributes->get('user_id');
        return $this->userEntityRepository->findOneByUserHandle($userHandle);
    }
}