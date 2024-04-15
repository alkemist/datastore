<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;

#[Table(name: "webauthn_credentials")]
#[Entity(repositoryClass: WebauthnCredentialRepository::class)]
class WebauthnCredential extends PublicKeyCredentialSource
{
    #[ORM\Column(nullable: true)]
    public ?bool $uvInitialized = null;

    #[Id]
    #[Column(unique: true)]
    #[GeneratedValue(strategy: "NONE")]
    private string $id;

    public function __construct(
        string    $publicKeyCredentialId,
        string    $type,
        array     $transports,
        string    $attestationType,
        TrustPath $trustPath,
        Uuid      $aaguid,
        string    $credentialPublicKey,
        string    $userHandle,
        int       $counter
    ) {
        $this->id = Ulid::generate();

        parent::__construct(
            $publicKeyCredentialId, $type, $transports, $attestationType, $trustPath, $aaguid, $credentialPublicKey,
            $userHandle, $counter
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
}