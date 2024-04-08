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
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectRepository      $projectRepository,
        private readonly StoreRepository        $storeRepository,
        private readonly ItemRepository         $itemRepository,
    ) {
    }

    #[Route(path: '/api/profile/{project_key}', name: 'api_profile', methods: ['GET'])]
    public function profile(#[CurrentUser] User $user, string $project_key): Response
    {
        $response = $this->buildResponse($user);
        $project = $this->projectRepository->findOneByKey($project_key);

        if (!$project) {
            return $response
                ->isUnprocessableEntity("Unknown projet '$project_key'")
                ->toJson();
        }

        // @TODO Faire pareil pour toutes les requètes
        if (!$user->hasAuthorization($project)) {
            return $response
                ->isUnprocessableEntity("No authorization for projet '$project_key'")
                ->toJson();
        }

        return $this->buildResponse($user)
            ->setItem($user->toJsonProfile($project))
            ->toJson();
    }

    private function buildResponse(User $user): ApiResponse
    {
        return (new ApiResponse())->setToken($user);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}', name: 'api_store_items', methods: ['GET'])]
    public function items(
        #[CurrentUser] User $user,
        string              $project_key,
        string              $store_key
    ): Response {
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        /** @var Item[] $items */
        $items = $this->itemRepository->findByStore($user, $store);

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
    #[Route(path: '/api/public/{project_key}/{store_key}', name: 'api_public_items', methods: ['GET'])]
    public function public_items(
        string $project_key,
        string $store_key
    ): Response {
        $response = new ApiResponse();

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        if ($store->isRestrictedByUser()) {
            return $response
                ->isUnprocessableEntity('Restricted store')
                ->toJson();
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

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}/search/items', name: 'api_search_items', methods: ['POST'])]
    public function search_items(
        #[CurrentUser] User $user,
        Request             $request,
        string              $project_key,
        string              $store_key
    ): Response {
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        $filters = $this->filterPostData($request, $store);

        $items = $this->itemRepository->findByFilters($user, $store, $filters);

        return $response
            ->setItems(
                array_map(
                    static fn(Item $item) => $item->toJson($store), $items
                )
            )
            ->toJson();
    }

    private function filterPostData(Request $request, Store $store): array
    {
        $content = $request->getContent();
        $values = json_decode($content, true);

        return ItemHelper::filterValues($values, $store->getFields()->toArray());
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}/search/item', name: 'api_search_item', methods: ['POST'])]
    public function search_item(
        #[CurrentUser] User $user,
        Request             $request,
        string              $project_key,
        string              $store_key
    ): Response {
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        $filters = $this->filterPostData($request, $store);

        $items = $this->itemRepository->findByFilters($user, $store, $filters);

        if (count($items) === 0) {
            return $response
                ->isUnprocessableEntity("No item")
                ->toJson();
        }

        if (count($items) > 1) {
            return $response
                ->isUnprocessableEntity("Multiples items")
                ->toJson();
        }

        $item = $items[0];

        return $response
            ->setItem($item->toJson($item->getStore()))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/store/{project_key}/{store_key}/{slug}', name: 'api_get_item', methods: ['GET'])]
    public function item(
        #[CurrentUser] User $user,
        string              $project_key,
        string              $store_key,
        string              $slug
    ): Response {
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
            $fields = $store->getFields()->toArray();
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        $item = $this->itemRepository->findOneByUserAndSlug($user, $store, $slug);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$slug'")
                ->toJson();
        }

        return $response
            ->setItem($item->toJson($item->getStore()))
            ->toJson();
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/public/{project_key}/{store_key}/{slug}', name: 'api_public_item', methods: ['GET'])]
    public function public_item(
        string $project_key,
        string $store_key,
        string $slug
    ): Response {
        $response = new ApiResponse();

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        if ($store->isRestrictedByUser()) {
            return $response
                ->isUnprocessableEntity('Restricted store')
                ->toJson();
        }

        $item = $this->itemRepository->findOneBySlug($store, $slug);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$slug'")
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
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        $values = $this->filterPostData($request, $store);

        $items = $this->itemRepository->findExistingItems($user, $store, $values);

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
        $response = $this->buildResponse($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        $store = $item->getStore();
        $values = $this->filterPostData($request, $item->getStore());

        $items = $this->itemRepository->findExistingItems($user, $store, $values, $id);

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
        $response = $this->buildResponse($user);

        try {
            $store = $this->getStore($project_key, $store_key);
        } catch (\Exception $exception) {
            return $response
                ->isUnprocessableEntity($exception->getMessage())
                ->toJson();
        }

        $item = new Item();

        if (!$this->checkName($store, $item, $request)) {
            return $response
                ->isUnprocessableEntity("Missing name")
                ->toJson();
        }

        $item->setAuthor($user);
        $item->setStore($store);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $response
            ->setItem($item->toJson($store))
            ->toJson();
    }

    private function checkName(Store $store, Item $item, Request $request): bool
    {
        $values = $this->filterPostData($request, $store);

        if (!isset($values['name'])) {
            return false;
        }

        $item->setName($values['name']);

        unset($values['name']);
        unset($values['id']);

        $item->setValues($values);

        return true;
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/item/{id}', name: 'api_post_item', methods: ['POST'])]
    public function update(#[CurrentUser] User $user, Request $request, Uuid $id): Response
    {
        $response = $this->buildResponse($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        if (!$item->getAuthor()->equal($user)) {
            return $response
                ->isUnprocessableEntity("You are not the author of item '$id'")
                ->toJson();
        }

        $store = $item->getStore();

        if (!$this->checkName($store, $item, $request)) {
            return $response
                ->isUnprocessableEntity("Missing name")
                ->toJson();
        }

        $this->entityManager->flush();

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
        $response = $this->buildResponse($user);

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $response
                ->isUnprocessableEntity("Unknown item '$id'")
                ->toJson();
        }

        if (!$item->getAuthor()->equal($user)) {
            return $response
                ->isUnprocessableEntity("You are not the author of item '$id'")
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