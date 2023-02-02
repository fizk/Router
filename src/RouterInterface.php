<?php

namespace Fizk\Router;

use Fizk\Router\RouteMatchInterface;
use Psr\Http\Message\RequestInterface;

interface RouterInterface
{
    public function match(RequestInterface $request): ?RouteMatchInterface;

    public function construct(string $path, ?array $arguments = []): ?string;
}