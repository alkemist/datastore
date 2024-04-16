<?php

namespace App\Controller\Admin;

use App\Entity\WebauthnCredential;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class WebauthnCredentialCrudController extends AbstractCrudController
{
    public function __construct()
    {

    }

    public static function getEntityFqcn(): string
    {
        return WebauthnCredential::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Key')
            ->setEntityLabelInPlural('Keys')
            ->setDefaultSort([])
            ->setSearchFields([])
            ->showEntityActionsInlined(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        $newAction =
            Action::new('admin_register', 'Créer Key')
                ->linkToRoute('admin_register')
                ->createAsGlobalAction();

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, $newAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('id');
            yield TextField::new('name');
            yield TextField::new('userHandle');
            yield TextField::new('type');
            yield ArrayField::new('transports');
            yield TextField::new('attestationType');
            yield NumberField::new('counter');
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('publicKeyCredentialId');
            //yield TextareaField::new('trust_path');
            yield TextField::new('aaguid');
            yield TextField::new('credentialPublicKey');
            yield ArrayField::new('otherUi');
        }

        if (Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('name');
        }
    }
}