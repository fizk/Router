<?php

namespace Fizk\Router;

interface RouteMatchInterface
{
    public function setParam(string $name, mixed $value): self;

    public function getParam(string $name, mixed $default = null): string;

    public function getParams(): array;

    public function setAttribute(string $name, string|int $value): self;

    public function getAttribute(string $name, string|int $default = null): array;

    public function getAttributes(): array;

    public function addPath(string $value);

    public function getPath(): string;
}
