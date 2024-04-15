<?php

namespace App\Entity;

use App\Enum\FieldTypeEnum;
use App\Helper\ItemHelper;
use App\Model\ParentEntity;
use App\Repository\FieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
#[ORM\UniqueConstraint(
    name   : 'field_idx',
    columns: ['store_id', 'key']
)]
#[ORM\HasLifecycleCallbacks]
class Field extends ParentEntity
{
    #[ORM\Column(type: Types::STRING, nullable: false, enumType: FieldTypeEnum::class)]
    protected ?FieldTypeEnum $type = null;

    private bool $valueChanged = false;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid|null $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Gedmo\Slug(fields: ['name'], unique: false)]
    #[Assert\NotBlank]
    private ?string $key = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $defaultValue = null;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Store $store = null;

    #[ORM\Column(nullable: true)]
    private ?bool $required = null;

    #[ORM\Column(nullable: true)]
    private ?bool $identify = null;

    public function __construct()
    {
        $this->type = FieldTypeEnum::String;
    }

    public function __serialize(): array
    {
        return [
            'key'          => $this->key,
            'defaultValue' => $this->defaultValue,
        ];
    }

    public function __toString(): string
    {
        return $this->getName() . ' : ' . $this->getType()->name;
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

    public function getType(): ?FieldTypeEnum
    {
        return $this->type;
    }

    public function setType(FieldTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
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

    /**
     * @throws Exception
     */
    public function getFormattedDefaultValue()
    {
        return $this->getIsNull() ? null
            : ItemHelper::formatValue($this, $this->defaultValue);
    }

    public function getIsNull()
    {
        return $this->defaultValue === null;
    }

    /**
     * Utilisé par easyAdmin
     * @param mixed $formattedDefaultValue
     * @return void
     */
    public function setFormattedDefaultValue(mixed $formattedDefaultValue)
    {
        $this->valueChanged = true;
        $this->defaultValue = $formattedDefaultValue;
        /*$this->defaultValue =
            ItemHelper::toString(
                $formattedDefaultValue,
                $this->getType()
            );*/
    }

    public function setIsNull(bool $isNull)
    {
        if (!$this->valueChanged && $isNull) {
            $this->defaultValue = null;
        }
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): static
    {
        $this->store = $store;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    public function isIdentify(): ?bool
    {
        return $this->identify;
    }

    public function setIdentify(?bool $identify): static
    {
        $this->identify = $identify;

        return $this;
    }
}
