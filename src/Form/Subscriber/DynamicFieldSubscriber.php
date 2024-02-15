<?php

namespace App\Form\Subscriber;

use App\Enum\FieldTypeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

abstract class DynamicFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetDataWithField(
        FormInterface $form, mixed $data, FieldTypeEnum $type, string $valueField, string $valueLabel,
        string        $checkField, string $checkLabel
    ) {
        // @TODO Rendre dynamique le "required" si le field est required
        switch ($type) {
            case FieldTypeEnum::Boolean:
                $form->add($valueField, ChoiceType::class, [
                    'label'   => $valueLabel, 'required' => false,
                    'choices' => [
                        'True'  => true,
                        'False' => false,
                    ],
                ]);
                break;
            case FieldTypeEnum::Datetime:
                $form->add($valueField, DateTimeType::class, ['label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::Int:
                $form->add($valueField, IntegerType::class, ['label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::Float:
                $form->add($valueField, NumberType::class, ['label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::String:
                $form->add($valueField, TextType::class, ['label' => $valueLabel, 'required' => false]);
                break;
            default:
                $form->add($valueField, TextareaType::class, ['label' => $valueLabel, 'required' => false]);
                break;
        }

        $form->add($checkField, CheckboxType::class, [
            'label'    => $checkLabel,
            'required' => false
        ]);
    }
}