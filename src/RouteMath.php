<?php

namespace Fizk\Router;

use Fizk\Router\RouteMathInterface;

class RouteMath implements RouteMathInterface
{
    private array $params = [];
    private array $path = [];
    private array $attributes = [];

    public function setParam(string $name, mixed $value): self
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function getParam(string $name, mixed $default = null): string
    {
        return array_key_exists($name, $this->params)
            ? $this->params[$name]
            : $default;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setAttribute(string $name, string|int $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttribute(string $name, string|int $default = null): array
    {
        return array_key_exists($name, $this->attributes)
            ? $this->attributes[$name]
            : $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addPath(string $value)
    {
        $this->path[] = $value;
    }

    public function getPath(): string
    {
        return implode('/', array_reverse($this->path));
    }
}
