<?php

namespace App\Form\Subscriber;

use App\Enum\FieldTypeEnum;
use App\Form\Type\JsonTextareaType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
        FormInterface $form, mixed $data, FieldTypeEnum $type, string $valueField, string $valueLabel
    ): void {
        $arrayOptions = ['allow_add' => true, 'allow_delete' => true, 'delete_empty' => true];

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
                $form->add($valueField, DateTimeType::class, [
                    'label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::Int:
                $form->add($valueField, IntegerType::class, [
                    'label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::Float:
                $form->add($valueField, NumberType::class, [
                    'label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::String:
                $form->add($valueField, TextType::class, [
                    'label' => $valueLabel, 'required' => false]);
                break;
            case FieldTypeEnum::Json:
                $form->add($valueField, JsonTextareaType::class, [
                    'label' => $valueLabel, 'required' => false, 'attr' => ['rows' => 5]]);
                break;
            case FieldTypeEnum::ArrayString:
                $form->add($valueField, CollectionType::class, [
                    'label' => $valueLabel, 'required' => false, 'entry_type' => TextType::class, ...$arrayOptions]);
                break;
            case FieldTypeEnum::ArrayInt:
                $form->add($valueField, CollectionType::class, [
                    'label' => $valueLabel, 'required' => false, 'entry_type' => IntegerType::class, ...$arrayOptions]);
                break;
            case FieldTypeEnum::ArrayFloat:
                $form->add($valueField, CollectionType::class, [
                    'label' => $valueLabel, 'required' => false, 'entry_type' => NumberType::class, ...$arrayOptions]);
                break;
            default:
                $form->add($valueField, TextareaType::class, [
                    'label' => $valueLabel, 'required' => false]);
                break;
        }
    }
}