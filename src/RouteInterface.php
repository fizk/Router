<?php

namespace Fizk\Router;

use Fizk\Router\RouteMatchInterface;
use Psr\Http\Message\RequestInterface;

interface RouteInterface
{
    /**
     * @throws \DomainException
     */
    public function match(RequestInterface $request): ?RouteMatchInterface;
}