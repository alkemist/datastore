<?php

namespace App\Entity;

use App\Model\TokenInterface;
use App\Repository\AuthorizationRepository;
use App\Trait\TokenTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorizationRepository::class)]
#[ORM\Table(name: '`authorization`')]
class Authorization extends TokenInterface
{
    use TokenTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'authorizations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $member = null;

    #[ORM\ManyToOne(inversedBy: 'authorizations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(nullable: true)]
    private ?int $tokenExpires = null;

    public function __toString(): string
    {
        return $this->getMember() . ' - ' . $this->getProject();
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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
