<?php

namespace App\Controller\Admin;

use App\Entity\Authorization;
use App\Form\Type\JsonCodeEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class AuthorizationCrudController extends AbstractCrudController
{
    public function __construct()
    {

    }

    public static function getEntityFqcn(): string
    {
        return Authorization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Authorization')
            ->setEntityLabelInPlural('Authorizations')
            ->setDefaultSort(['member' => 'ASC'])
            ->setSearchFields(['member.username', 'project.name'])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('project')
            ->setColumns(6);

        yield AssociationField::new('member')
            ->setColumns(6);

        if (Crud::PAGE_INDEX === $pageName) {
            yield TimeField::new('tokenExpiresDiffDate')
                ->setLabel('Token expire');
        }

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield NumberField::new('tokenExpires')
                ->setColumns(4);

            yield TextField::new('token')
                ->setColumns(8);

            yield CodeEditorField::new('data')
                ->setFormType(JsonCodeEditorType::class)
                ->setColumns(12);
        }
    }
}