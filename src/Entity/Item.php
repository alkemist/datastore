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
#[ORM\UniqueConstraint(
    name   : 'item_slug_idx',
    columns: ['store_id', 'slug']
)]
class Item
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid|null $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

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
     * @param Store $store
     * @return array
     * @throws Exception
     */
    public function toJson(Store $store): array
    {
        return [
            'id'   => $this->getId(),
            'slug' => $this->getSlug(),
            ...ItemHelper::formatValues($store->getFields()->toArray(), $this, true)
        ];
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getStore()->getProject() . ' / ' . $this->getStore() . ' / ' . $this->getSlug();
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

    /**
     * @param $jsonValues
     * @return void
     * @throws Exception
     */
    public function setItemFieldValues($jsonValues): void
    {
        $this->setValues(
            array_combine(
                array_map(static fn(ItemFieldValue $itemFieldValue) => $itemFieldValue->getField()->getName(),
                    $jsonValues),
                array_map(
                    static fn(ItemFieldValue $itemFieldValue) => $itemFieldValue->getValue(),
                    $jsonValues
                ),
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