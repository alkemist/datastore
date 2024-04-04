<?php

namespace App\Filter;

use App\Form\Type\JsonFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class JsonFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormTypeOption('mapped', false)
            ->setFormType(JsonFilterType::class);
    }

    public function apply(
        QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto
    ): void {
        $key = $filterDataDto->getProperty();
        $path = $filterDataDto->getValue();
        $value = $filterDataDto->getValue2();

        $queryBuilder->andWhere(
            $queryBuilder->expr()->like(
                sprintf("JSON_GET_TEXT(%s.%s, :key_$path)", $filterDataDto->getEntityAlias(), $key),
                ":val_$path"
            )
        )
            ->setParameter("key_$path", $path)
            ->setParameter("val_$path", "%" . $value . "%");
    }
}