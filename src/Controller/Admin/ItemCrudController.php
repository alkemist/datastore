<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use App\Form\Type\ItemFieldValueType;
use App\Helper\ItemHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Item')
            ->setEntityLabelInPlural('Items')
            ->setDefaultSort(['store' => 'ASC'])
            ->setSearchFields(['id', 'key', 'name', 'store.key'])
            ->showEntityActionsInlined(true);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function configureActions(Actions $actions): Actions
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $actions
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(
                Crud::PAGE_INDEX, Action::new('redirectToStore', 'Edit Store')
                ->linkToCrudAction('redirectToStore')
                ->createAsGlobalAction()
            );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function redirectToStore(AdminContext $context)
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $store = $this->getStore();
        $projectId = $store->getProject()->getId();

        return $this->redirect(
            $adminUrlGenerator->setController(StoreCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId($store->getId())
                ->set('filters[project][comparison]', '=')
                ->set('filters[project][value]', $projectId)
                ->generateUrl()
        );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('store');
    }

    public function configureFields(string $pageName): iterable
    {
        $this->configurePageNames($this->getStore());

        if (Crud::PAGE_NEW !== $pageName) {
            yield TextField::new('id')
                ->setDisabled()
                ->setColumns(12);
        }


        if (Crud::PAGE_INDEX !== $pageName) {
            yield FormField::addFieldset('Values');
            yield CollectionField::new('itemFieldValues')
                ->setRequired(Crud::PAGE_EDIT !== $pageName)
                ->allowAdd(false)
                ->allowDelete(false)
                ->setEntryType(ItemFieldValueType::class)
                ->setFormTypeOption('entry_options.attr.class', 'flex')
                ->renderExpanded()
                ->setLabel(false)
                ->setColumns(12);
        } else {
            yield TextAreaField::new('stringValues')
                ->setLabel("Values")
                ->renderAsHtml();
        }
    }

    public function createEntity(string $entityFqcn): Item
    {
        $store = $this->getStore();
        $fields = $store->getFields()->toArray();

        $item = new Item();
        $item->setStore($store);
        $item->setValues(ItemHelper::defaultValues($fields, $item));

        return $item;
    }
}