<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class UserCrudController extends AbstractCrudController
{
    public function __construct()
    {

    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setDefaultSort(['username' => 'ASC'])
            ->setSearchFields(['username', 'email', 'googleId'])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('username')->add('email')->add('roles');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username')
            ->setColumns(2);
        yield TextField::new('email')
            ->setColumns(3);
        yield ArrayField::new('roles')
            ->setColumns(3);

        if (Crud::PAGE_INDEX === $pageName) {
            yield TimeField::new('tokenExpiresDiffDate')
                ->setLabel('Token expire');

            yield ArrayField::new('authorizationProjects')
                ->setLabel('Authorizations');
        }


        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('googleId')
                ->setColumns(4);

            yield TextField::new('googleRefreshToken')
                ->setColumns(12);

            yield NumberField::new('tokenExpires')
                ->setColumns(4);

            yield TextField::new('token')
                ->setColumns(8);

            yield CollectionField::new('authorizations')
                ->setColumns(12)
                ->renderExpanded()
                ->useEntryCrudForm();
        }
    }
}