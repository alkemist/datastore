<?php

namespace App\Form\Type;

use App\Enum\FieldTypeEnum;
use App\Form\Subscriber\ItemFieldValueSubscriber;
use App\Model\ItemFieldValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemFieldValueType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                                   'data_class' => ItemFieldValue::class
                               ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', EnumType::class, [
            'disabled'     => true,
            'class'        => FieldTypeEnum::class,
            'choice_label' => function (FieldTypeEnum $choice, $key, $value) {
                return $choice->toString();
            }
        ]);

        $builder->addEventSubscriber(new ItemFieldValueSubscriber());
    }

    public function getParent(): string
    {
        return FormType::class;
    }
}