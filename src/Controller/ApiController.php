<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Store;
use App\Entity\User;
use App\Helper\ItemHelper;
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

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}', name: 'api_get_items', methods: ['GET'])]
    public function items(
        #[CurrentUser] User $user,
        Request             $request,
        string              $project_key,
        string              $store_key
    ): Response {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $items = $store->getItems()->toArray();

        return $response
            ->setItems(
                array_map(
                    static fn(Item $item) => $item->toJson($store), $items
                )
            )
            ->toJson();
    }

    private function getStore(string $project_key, string $store_key): Store
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

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}/search', name: 'api_search_items', methods: ['POST'])]
    public function search(
        #[CurrentUser] User $user,
        Request             $request,
        string              $project_key,
        string              $store_key
    ): Response {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $filters = $this->filterPostData($request, $store, false);

        $items = $this->itemRepository->findByValues($filters);

        return $response
            ->setItems(
                array_map(
                    static fn(Item $item) => $item->toJson($store), $items
                )
            )
            ->toJson();
    }

    private function filterPostData(Request $request, Store $store, bool $skipId = true): array
    {
        $content = $request->getContent();
        $values = json_decode($content, true);

        return ItemHelper::filterValues($values, $store->getFields()->toArray(), $skipId);
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

        return $response
            ->setItem($item->toJson($item->getStore()))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/store/{project_key}/{store_key}/exist', name: 'api_exist_create_item', methods: ['PUT'])]
    public function exist_create(
        #[CurrentUser] User $user, Request $request, string $project_key, string $store_key
    ): Response {
        $response = (new ApiResponse())
            ->setToken($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $values = $this->filterPostData($request, $store, false);

        $items = $this->itemRepository->findByValues($values);

        return $response
            ->setResponse(count($items) > 0)
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/item/{id}/exist', name: 'api_exist_update_item', methods: ['POST'])]
    public function exist_update(
        #[CurrentUser] User $user, Request $request, Uuid $id
    ): Response {
        $response = (new ApiResponse())
            ->setToken($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        $values = $this->filterPostData($request, $item->getStore(), false);

        $items = $this->itemRepository->findByValues($values, $id);

        return $response
            ->setResponse(count($items) > 0)
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
        } catch (\Exception $exception) {
            $response->isUnprocessableEntity($exception->getMessage());
            return $response->toJson();
        }

        $values = $this->filterPostData($request, $store);

        $item = new Item();
        $item->setStore($store);
        $item->setValues($values);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $response
            ->setItem($item->toJson($store))
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


        $store = $item->getStore();

        $values = $this->filterPostData($request, $store);
        $item->setValues($values);

        return $response
            ->setItem($item->toJson($store))
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

        $store = $item->getStore();

        $response
            ->setItem($item->toJson($store));

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $response->toJson();
    }
}