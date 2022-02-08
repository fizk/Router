<?php

namespace Fizk\Router;

use Fizk\Router\RouteMathInterface;
use Psr\Http\Message\RequestInterface;

interface RouteInterface
{
    public function match(RequestInterface $request, int $offset = 0): ?RouteMathInterface;
}