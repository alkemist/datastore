<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User extends OAuthUser
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid|null $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleRefreshToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $googleExpires = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $data = null;

    public function __construct($username = '', array $roles = ['ROLE_USER'])
    {
        parent::__construct($username, $roles);
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function isExpired(): bool
    {
        return !$this->getGoogleRefreshToken() || $this->getGoogleExpires() < time();
    }

    public function getGoogleRefreshToken(): ?string
    {
        return $this->googleRefreshToken;
    }

    public function setGoogleRefreshToken(?string $googleRefreshToken): static
    {
        $this->googleRefreshToken = $googleRefreshToken;

        return $this;
    }

    public function getGoogleExpires(): ?int
    {
        return $this->googleExpires;
    }

    public function setGoogleExpires(?int $googleExpires): static
    {
        $this->googleExpires = $googleExpires;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getGoogleExpiresDiffDate(): ?DateTime
    {
        return $this->googleExpires > time()
            ? DateTime::createFromFormat('U', $this->googleExpires - time())
            : null;
    }

    public function toArray()
    {
        return [
            'id'       => $this->id,
            'email'    => $this->email,
            'username' => $this->username,
            'data'     => $this->data,
        ];
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
