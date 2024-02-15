<?php

namespace App\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class JsonCodeEditorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($object) {
                        return is_array($object) && count($object) === 0
                            ? "{}"
                            : json_encode($object, JSON_PRETTY_PRINT);
                    },
                    function (string|null $string) {
                        $string = !$string || strlen($string) === 0 ? '{}' : $string;
                        $string = preg_replace('/\'/', '"', $string);
                        $string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);
                        return json_decode(
                            $string,
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        );
                    },
                )
            );
    }

    public function getParent(): string
    {
        return CodeEditorType::class;
    }
}