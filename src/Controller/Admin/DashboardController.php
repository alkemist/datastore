<?php

namespace App\Controller\Admin;

use App\Entity\Authorization;
use App\Entity\Company;
use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Store;
use App\Entity\User;
use App\Entity\WebauthnCredential;
use App\Repository\ProjectRepository;
use App\Repository\StoreRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private StoreRepository            $storeRepository,
        private ProjectRepository          $projectRepository,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator->setController(ProjectCrudController::class)
                ->generateUrl()
        );
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Datastore');
    }

    public function configureActions(): Actions
    {
        $actions = parent::configureActions();
        return $actions
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Access');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Keys', 'fas fa-user', WebauthnCredential::class);
        yield MenuItem::linkToCrud('Authorizations', 'fas fa-door-open', Authorization::class);


        yield MenuItem::section('Datastores');
        yield MenuItem::linkToCrud('Projects', 'fas fa-folder', Project::class);

        foreach ($this->projectRepository->findAll() as $project) {
            yield MenuItem::section($project->getName());

            yield MenuItem::linkToCrud(
                'Stores', 'fa fa-sitemap', Store::class
            )
                ->setQueryParameter('filters[project][comparison]', '=')
                ->setQueryParameter('filters[project][value]', $project->getId());

            foreach ($this->storeRepository->findByProject($project) as $store) {
                yield MenuItem::linkToCrud(
                    $store->getName(), 'fas fa-list', Item::class
                )
                    ->setQueryParameter('filters[store][comparison]', '=')
                    ->setQueryParameter('filters[store][value]', $store->getId());

            }
        }
    }

    #[Route('/admin/register', name: 'admin_register')]
    public function report(Request $request): Response
    {
        return $this->render('admin/register.html.twig', [
            'creationSuccessRedirectUri' => $this->adminUrlGenerator
                ->setController(WebauthnCredentialCrudController::class)
                ->generateUrl()
        ]);
    }
}
