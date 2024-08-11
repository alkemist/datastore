<?php

namespace App\Trait;


use DateTime;
use Exception;
use Symfony\Component\Uid\Uuid;

trait TokenTrait
{
    const MAX_AGE = 3600 * 24;

    private ?string $token = null;
    private ?int $tokenExpires = null;

    /**
     * @throws Exception
     */
    public function getTokenExpiresDiffDate(): ?DateTime
    {
        return $this->tokenExpires > time()
            ? DateTime::createFromFormat('U', $this->tokenExpires - time())
            : null;
    }

    public function updateToken(): static
    {
        $this->token = Uuid::v7()->jsonSerialize();
        $this->tokenExpires = time() + self::MAX_AGE;

        return $this;
    }

    public function isExpired(): bool
    {
        return !$this->getToken() || !$this->getTokenExpires() || $this->getTokenExpires() < time();
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken($token = ''): static
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenExpires(): ?int
    {
        return $this->tokenExpires;
    }

    public function setTokenExpires(?int $tokenExpires): static
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }
}