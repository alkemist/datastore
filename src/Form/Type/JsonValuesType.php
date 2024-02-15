<?php

namespace App\Form\Type;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonValuesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                                   'allow_add'    => false,
                                   'allow_delete' => false,
                                   'delete_empty' => false,
                                   'entry_type'   => TextType::class,
                               ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}