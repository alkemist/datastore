<?php

namespace App\Entity;

use App\Model\ParentEntity;
use App\Repository\StoreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoreRepository::class)]
#[ORM\UniqueConstraint(
    name   : 'store_idx',
    columns: ['project_id', 'key']
)]
class Store extends ParentEntity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid|null $id = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['name'], unique: false)]
    #[Assert\NotBlank]
    private ?string $key = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'store', targetEntity: Field::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(["key" => "ASC"])]
    private Collection $fields;

    #[ORM\ManyToOne(inversedBy: 'stores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\OneToMany(mappedBy: 'store', targetEntity: Item::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(["id" => "ASC"])]
    private Collection $items;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function __serialize(): array
    {
        return [
            'key' => $this->key,
        ];
    }

    public function __toString(): string
    {
        return $this->getProject() . ' / ' . $this->getName();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFieldKeys(): array
    {
        return array_map(static fn(Field $field) => $field->getName(), $this->getFields()->toArray());
    }

    /**
     * @return Collection<int, Field>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function addField(Field $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setStore($this);
        }

        return $this;
    }

    public function removeField(Field $field): static
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getStore() === $this) {
                $field->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setStore($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getStore() === $this) {
                $item->setStore(null);
            }
        }

        return $this;
    }
}
