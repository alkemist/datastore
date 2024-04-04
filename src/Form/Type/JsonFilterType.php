<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextType::class, ['label' => 'Key', 'label_attr' => ['style' => 'display:block;']])
            ->add('comparison', HiddenType::class)
            ->add('value2', TextType::class, ['label' => 'Value', 'label_attr' => ['style' => 'display:block;']]);
    }

    public function getParent(): string
    {
        return FormType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                                   'attr' => ['class' => 'jsonFilter']
                               ]);
        parent::configureOptions($resolver);
    }
}