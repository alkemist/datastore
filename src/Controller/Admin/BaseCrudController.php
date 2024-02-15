<?php

namespace App\Controller\Admin;

use App\Model\ParentEntity;
use App\Repository\ProjectRepository;
use App\Repository\StoreRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BaseCrudController extends AbstractCrudController
{
    public function __construct(
        protected AdminContextProvider $adminContextProvider,
        protected ProjectRepository    $projectRepository,
        private StoreRepository        $storeRepository,
        private TranslatorInterface    $translator,
    ) {
    }

    public function getProject()
    {
        $projectId = $this->getFilterValue("project");
        if (!$projectId) {
            return null;
        }

        return $this->projectRepository->find($projectId);
    }

    public function getFilterValue(string $filterName)
    {
        $context = $this->adminContextProvider->getContext();
        $queryParams = $context->getRequest()->query
            ->all();

        if (!isset($queryParams['filters'])) {
            return null;
        }

        return $queryParams['filters'][$filterName]['value'];
    }

    public function getStore()
    {
        $storeId = $this->getFilterValue("store");

        if (!$storeId) {
            return null;
        }

        return $this->storeRepository->find($storeId);
    }

    public function configurePageNames(?ParentEntity $parent)
    {
        if ($parent) {
            $crud = $this->adminContextProvider->getContext()->getCrud();
            $pluralLabel = $crud->getEntityLabelInPlural()->trans($this->translator);
            $singularLabel = $crud->getEntityLabelInSingular()->trans($this->translator);

            $crud->setCustomPageTitle(Crud::PAGE_INDEX, "[{$parent}] $pluralLabel");
            $crud->setCustomPageTitle(Crud::PAGE_NEW, "[{$parent}] Create $singularLabel");
            $crud->setCustomPageTitle(Crud::PAGE_EDIT, "[{$parent}] Update $singularLabel");
        }
    }
}