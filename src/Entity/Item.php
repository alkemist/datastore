<?php

namespace App\Entity;

use App\Helper\ItemHelper;
use App\Model\ItemFieldValue;
use App\Repository\ItemRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\UniqueConstraint(
    name   : 'item_idx',
    columns: ['store_id', 'id']
)]
class Item
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid|null $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Store $store = null;

    #[ORM\Column()]
    private array $values = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: "create")]
    private ?DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: "update")]
    private ?DateTimeInterface $updated = null;

    /**
     * @param Field[] $fields
     * @return array
     * @throws Exception
     */
    public function toJson(array $fields): array
    {
        return [
            'id' => $this->id,
            ...ItemHelper::formatValues($fields, $this, true)
        ];
    }

    public function __toString(): string
    {
        return $this->getStore()->getProject() . ' / ' . $this->getStore() . ' / ' . $this->getId();
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

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * @throws Exception
     */
    public function getItemFieldValues(): array
    {
        return array_map(
            fn(Field $field) => new ItemFieldValue(
                $field,
                ItemHelper::formatValue($field, ItemHelper::getValue($field, $this)),
                ItemHelper::getValue($field, $this) === null,
            ),
            $this->getStore()->getFields()->toArray()
        );
    }

    public function getStringValues(): string
    {
        return json_encode($this->getValues(), JSON_PRETTY_PRINT);
    }

    public function getValues(): ?array
    {
        return $this->values;
    }

    public function setValues(?array $values): static
    {
        $this->values = $values;

        return $this;
    }

    public function setItemFieldValues($jsonValues): void
    {
        $this->setValues(
            array_combine(
                array_map(static fn(ItemFieldValue $itemFieldValue) => $itemFieldValue->getField()->getKey(),
                    $jsonValues),
                array_map(static fn(ItemFieldValue $itemFieldValue) => ItemHelper::toString(
                    $itemFieldValue->getValue(),
                    $itemFieldValue->getType()
                ),
                    $jsonValues),
            )
        );
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?DateTimeInterface $updated): static
    {
        $this->updated = $updated;

        return $this;
    }
}