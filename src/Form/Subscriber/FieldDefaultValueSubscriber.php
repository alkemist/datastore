<?php

namespace App\Form\Subscriber;

use App\Entity\Field;
use App\Enum\FieldTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormEvent;

class FieldDefaultValueSubscriber extends DynamicFieldSubscriber
{
    public function preSetData(FormEvent $event)
    {
        /** @var Field $data */
        $data = $event->getData();
        $form = $event->getForm();

        $form->add('type', EnumType::class, [
            'class'        => FieldTypeEnum::class,
            'disabled'     => $data !== null,
            'choice_label' => function (FieldTypeEnum $choice, $key, $value) {
                return $choice->toString();
            }
        ]);

        if ($data !== null) {
            parent::preSetDataWithField(
                $form, $data, $data->getType(),
                'formattedDefaultValue', 'default value',
            );

            $form->add('isNull', CheckboxType::class, [
                'label'    => 'Null value',
                'required' => false
            ]);
        }

        $form->add('required', CheckboxType::class, [
            'required' => false
        ]);

        $form->add('identify', CheckboxType::class, [
            'label'    => 'Unique',
            'required' => false
        ]);
    }
}