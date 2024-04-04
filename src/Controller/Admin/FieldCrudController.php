<?php

namespace App\Controller\Admin;

use App\Entity\Field;
use App\Enum\FieldTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class FieldCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Field::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Field')
            ->setEntityLabelInPlural('Fields')
            ->setDefaultSort(['store' => 'ASC'])
            ->setSearchFields(['key', 'name', 'type', 'defaultValue'])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('store');
    }

    public function configureFields(string $pageName): iterable
    {
        $this->configurePageNames($this->getStore());
        /** @var Field $field */
        $field = $this->adminContextProvider->getContext()->getEntity()->getInstance();

        yield SlugField::new('key')->setColumns(2)
            ->setTargetFieldName('name');

        yield TextField::new('name')->setColumns(2);

        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...FieldTypeEnum::choices()]);

            yield TextAreaField::new('defaultValue')
                ->setLabel('Default value')
                ->renderAsHtml();
        }

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield ChoiceField::new('type')
                ->setColumns(2)
                ->setFormType(EnumType::class)
                ->setFormTypeOption('class', FieldTypeEnum::class)
                ->setFormTypeOption('choice_label', function (FieldTypeEnum $choice, $key, $value) {
                    return $choice->toString();
                });

            yield CodeEditorField::new('defaultValue')
                ->setLabel('Default value')
                ->setColumns(4);

            yield BooleanField::new('required')
                ->setColumns(4);

            yield BooleanField::new('identify')
                ->setColumns(4);
        }
    }

    public function createEntity(string $entityFqcn): Field
    {
        $store = $this->getStore();

        $field = new Field();
        $field->setStore($store);

        return $field;
    }
}