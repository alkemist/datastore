<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Authorization::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $authorizations;

    public function __construct($username = '', array $roles = ['ROLE_USER'])
    {
        parent::__construct($username, $roles);
        $this->authorizations = new ArrayCollection();
    }

    public function __toString(): string
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

    public function toJson(): array
    {
        return [
            'username' => $this->getUsername()
        ];
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
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

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function addAuthorization(Authorization $authorization): static
    {
        if (!$this->authorizations->contains($authorization)) {
            $this->authorizations->add($authorization);
            $authorization->setMember($this);
        }

        return $this;
    }

    public function removeAuthorization(Authorization $authorization): static
    {
        if ($this->authorizations->removeElement($authorization)) {
            // set the owning side to null (unless already changed)
            if ($authorization->getMember() === $this) {
                $authorization->setMember(null);
            }
        }

        return $this;
    }

    public function hasAuthorization(Project $project): bool
    {
        foreach ($this->getAuthorizations() as $authorization) {
            if ($authorization->getProject()->getId() === $project->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, Authorization>
     */
    public function getAuthorizations(): Collection
    {
        return $this->authorizations;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getAuthorizationProjects(): array
    {
        return array_map(static fn(Authorization $auth) => $auth->getProject(), $this->getAuthorizations()->toArray());
    }

    public function toJsonProfile(Project $project): array
    {
        /** @var Authorization $authorization */
        $authorization = current(
            array_filter(
                $this->getAuthorizations()->toArray(),
                fn($authorization) => $authorization->getProject()->getId() === $project->getId()
            )
        );

        return [
            'id'       => $this->id,
            'email'    => $this->email,
            'username' => $this->username,
            'data'     => $authorization->getData(),
        ];
    }

    public function equal(User $user): bool
    {
        return $this->getId()->equals($user->getId());
    }
}
