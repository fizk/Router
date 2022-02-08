<?php

namespace Fizk\Router;

use Fizk\Router\RouteMathInterface;
use Fizk\Router\RouteMath;
use Fizk\Router\RouteInterface;
use Psr\Http\Message\RequestInterface;
use IteratorAggregate;
use Traversable;
use ArrayIterator;

class Route implements RouteInterface, IteratorAggregate
{
    private string $name;
    private string $route;
    private array $params = [];
    private array $children = [];
    private $position = 0;

    public function __construct(string $name, string $route, array $params = [])
    {
        $this->route = $route;
        $this->name = $name;
        $this->params = $params;
    }

    public function match(RequestInterface $request, int $offset = 0): ?RouteMathInterface
    {
        $path = $request->getUri()->getPath();
        $routeExp = '/' . str_replace('/', '\/', $this->route) . '/A';

        $matchResult = preg_match($routeExp, $path, $matches, 0, $offset);

        if (!$matchResult) {
            return null;
        }

        if (strlen($path) <= $offset + strlen($matches[0])) {
            $match = new RouteMath();
            foreach ($matches as $k => $v) {
                if (!is_numeric($k)) {
                    $match->setAttribute($k, $v);
                }
            }

            foreach($this->params as $k => $v) {
                $match->setParam($k, $v);
            }

            return $match;
        }

        foreach ($this as $key => $value) {
            $match = $value->match($request, $offset + strlen($matches[0]));
            if ($match) {
                $match->addPath($key);

                foreach ($matches as $k => $v) {
                    if (!is_numeric($k)) {
                        $match->setAttribute($k, $v);
                    }
                }
                return $match;
            }
        }

        return null;
    }

    public function addRoute(RouteInterface $route): self
    {
        $this->children[] = $route;
        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->children);
    }

    public function current(): mixed
    {
        return $this->children[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }
}