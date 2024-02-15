<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Form\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StoreCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Store::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $this->configurePageNames($this->getProject());

        yield SlugField::new('key')
            ->setTargetFieldName('name')
            ->setColumns(6);

        yield TextField::new('name')
            ->setColumns(6);

        if (Crud::PAGE_INDEX !== $pageName) {
            yield FormField::addFieldset('Fields');
            yield CollectionField::new('fields')
                ->setRequired(true)
                ->setFormTypeOption('allow_add', true)
                ->setFormTypeOption('allow_delete', true)
                ->setEntryType(FieldType::class)
                ->setFormTypeOption('entry_options.attr.class', 'flex')
                ->setFormTypeOption('attr.class', 'store_fields')
                ->renderExpanded()
                ->setLabel(false)
                ->setColumns(12);
        }

        if (Crud::PAGE_INDEX === $pageName) {
            yield ArrayField::new('fieldKeys')
                ->setLabel('Fields');
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Store')
            ->setEntityLabelInPlural('Stores')
            ->setDefaultSort(['project' => 'ASC'])
            ->setSearchFields(['project.key', 'key', 'name', 'fields.name', 'fields.key'])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('project');
    }

    public function createEntity(string $entityFqcn): Store
    {
        $project = $this->getProject();

        $store = new Store();
        $store->setProject($project);

        return $store;
    }
}