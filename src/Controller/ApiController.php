<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Model\ApiResponse;
use App\Repository\ItemRepository;
use App\Repository\ProjectRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

class ApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository      $projectRepository,
        private StoreRepository        $storeRepository,
        private ItemRepository         $itemRepository,
    ) {
    }

    #[Route(path: '/api/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(#[CurrentUser] User $user): Response
    {

        return (new ApiResponse())
            ->setToken($user)
            ->setItem($user->toArray())
            ->toJson();
    }

    #[Route(path: '/api/store/{project_key}/{store_key}', name: 'api_get_items', methods: ['GET'])]
    public function items(#[CurrentUser] User $user, string $project_key, string $store_key): Response
    {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
            $fields = $store->getFields()->toArray();
            $items = $store->getItems()->toArray();
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }


        return $response
            ->setItems(
                array_map(static fn(Item $item) => $item->toJson($fields), $items)
            )
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/item/{id}', name: 'api_get_item', methods: ['GET'])]
    public function item(#[CurrentUser] User $user, string $project_key, string $store_key, string $id): Response
    {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
            $fields = $store->getFields()->toArray();
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        $fields = $item->getStore()->getFields()->toArray();

        return $response
            ->setItem($item->toJson($fields))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/store/{project_key}/{store_key}', name: 'api_put_item', methods: ['PUT'])]
    public function create(
        #[CurrentUser] User $user, Request $request, string $project_key, string $store_key
    ): Response {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
            $fields = $store->getFields()->toArray();
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $item = new Item();
        $item->setStore($store);
        $item->setValues($request->request->all());

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $response
            ->setItem($item->toJson($fields))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/item/{id}', name: 'api_post_item', methods: ['POST'])]
    public function update(#[CurrentUser] User $user, Request $request, Uuid $id): Response
    {
        $response = (new ApiResponse())
            ->setToken($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        $item->setValues(
            array_filter(
                $request->request->all(),
                fn($key) => $key !== 'id',
                ARRAY_FILTER_USE_KEY
            )
        );

        $fields = $item->getStore()->getFields()->toArray();

        return $response
            ->setItem($item->toJson($fields))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/item/{id}', name: 'api_delete_item', methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, Request $request, Uuid $id): Response
    {
        $response = (new ApiResponse())
            ->setToken($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        $fields = $item->getStore()->getFields()->toArray();

        $response
            ->setItem($item->toJson($fields));

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $response->toJson();
    }

    private function getStore(string $project_key, string $store_key)
    {
        $projectEntity = $this->projectRepository->findOneByKey($project_key);

        if (!$projectEntity) {
            throw new Exception("Unknown projet '$project_key'");
        }

        $storeEntity = $this->storeRepository->findOneByKey($projectEntity, $store_key);

        if (!$storeEntity) {
            throw new Exception("Unknown store_key '$project_key / $project_key'");
        }

        return $storeEntity;
    }
}