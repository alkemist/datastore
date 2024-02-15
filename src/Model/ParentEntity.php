<?php

namespace App\Model;

use Symfony\Component\Uid\Uuid;

abstract class ParentEntity
{
    abstract function getId(): ?Uuid;

    abstract function getName(): ?string;

    abstract function getKey(): ?string;

    abstract function __serialize(): array;

    abstract function __toString(): string;

}