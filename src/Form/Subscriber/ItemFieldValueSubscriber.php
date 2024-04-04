<?php

namespace App\Form\Subscriber;

use App\Model\ItemFieldValue;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;

class ItemFieldValueSubscriber extends DynamicFieldSubscriber
{
    public function preSetData(FormEvent $event)
    {
        /** @var ItemFieldValue $data */
        $data = $event->getData();

        if ($data !== null) {
            $form = $event->getForm();

            $form->add('defaultValue', TextType::class, [
                'disabled' => true
            ]);

            parent::preSetDataWithField(
                $form, $data, $data->getType(),
                'value', 'value',
            );

            $form->add('isDefaultValue', CheckboxType::class, [
                'label'    => 'Default value',
                'required' => false
            ]);
        }
    }
}