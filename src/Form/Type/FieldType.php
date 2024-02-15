<?php

namespace App\Form\Type;

use App\Entity\Field;
use App\Form\Subscriber\FieldDefaultValueSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                                   'data_class' => Field::class
                               ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class);

        $builder->addEventSubscriber(new FieldDefaultValueSubscriber());
    }

    public function getParent(): string
    {
        return FormType::class;
    }
}