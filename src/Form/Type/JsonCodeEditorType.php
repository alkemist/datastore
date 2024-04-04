<?php

namespace App\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;

class JsonCodeEditorType extends JsonTextareaType
{
    public function getParent(): string
    {
        return CodeEditorType::class;
    }
}