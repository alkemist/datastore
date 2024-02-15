<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Form\Type\StoreType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectCrudController extends AbstractCrudController
{
    public function __construct()
    {

    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Project')
            ->setEntityLabelInPlural('Projects')
            ->setDefaultSort(['key' => 'ASC'])
            ->setSearchFields(['key', 'name', 'stores.key', 'stores.name'])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield SlugField::new('key')
            ->setTargetFieldName('name')
            ->setColumns(6);
        yield TextField::new('name')->setColumns(6);

        if (Crud::PAGE_INDEX !== $pageName) {
            yield FormField::addFieldset('Stores');
            yield CollectionField::new('stores')
                ->setRequired(true)
                ->setFormTypeOption('allow_add', true)
                ->setFormTypeOption('allow_delete', false)
                ->setEntryType(StoreType::class)
                ->setLabel(false)
                ->setColumns(12);
        }

        if (Crud::PAGE_INDEX === $pageName) {
            yield ArrayField::new('storeKeys')
                ->setLabel('Stores');
        }
    }
}