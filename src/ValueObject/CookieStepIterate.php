<?php

declare(strict_types=1);

namespace Wnull\Selective\ValueObject;

final class CookieStepIterate
{
    private int $step;
    private string $cookieName;
    private string $cookieValue;
    private bool $response;

    public function __construct(
        int $step,
        string $cookieName,
        string $cookieValue,
        bool $response
    ) {
        $this->step = $step;
        $this->cookieName = $cookieName;
        $this->cookieValue = $cookieValue;
        $this->response = $response;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    public function getCookieValue(): string
    {
        return $this->cookieValue;
    }

    public function getResponse(): bool
    {
        return $this->response;
    }

    public function isNeeded(): bool
    {
        return $this->response === false;
    }
}
