<?php

namespace App\Model;

use App\Entity\User;
use ArrayObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends ArrayObject
{
    private int $status = 200;

    function __construct($array = [])
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @throws \Exception
     */
    function setToken(User $user): static
    {
        $this->offsetSet('token', $user->getCurrentAuth()->getToken());
        return $this;
    }

    function setResponse(mixed $response): static
    {
        $this->offsetSet('response', $response);
        return $this;
    }

    function setItem(mixed $item): static
    {
        $this->offsetSet('item', $item);
        return $this;
    }

    function setItems(array $items): static
    {
        $this->offsetSet('items', $items);
        return $this;
    }

    function getStatus(): int
    {
        return $this->status;
    }

    function isNotLogged(): static
    {
        $this->status = Response::HTTP_UNAUTHORIZED;
        $this->setMessage('User not logged');
        return $this;
    }

    private function setMessage(string $message): static
    {
        $this->offsetSet('message', $message);
        return $this;
    }

    function isUnauthorized($error, ?int $code = null): static
    {
        $this->status = $code !== null && $code > 0 ? $code : Response::HTTP_FORBIDDEN;
        $this->setMessage($error);
        return $this;
    }

    function isUnprocessableEntity($error): static
    {
        $this->status = Response::HTTP_UNPROCESSABLE_ENTITY;
        $this->setMessage($error);
        return $this;
    }

    function toJson(): JsonResponse
    {
        return new JsonResponse($this, $this->status);
    }
}