<?php

namespace Fizk\Router;

use Fizk\Router\RouteMatchInterface;
use Fizk\Router\RouteMatch;
use Fizk\Router\RouteInterface;
use Psr\Http\Message\RequestInterface;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use DomainException;

class Route implements RouteInterface, IteratorAggregate, JsonSerializable
{
    private string $name;
    private string $pattern;
    private array $params = [];
    private array $children = [];
    private $position = 0;

    public function __construct(string $name, string $pattern, array $params = [])
    {
        $this->pattern = $pattern;
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * @throws \DomainException
     */
    public function match(RequestInterface $request): RouteMatchInterface 
    {
        $path = $request->getUri()->getPath();
        return $this->_match($request) ?: throw new DomainException("Path [$path] did not match any routes");
    }

    public function _match(RequestInterface $request, int $offset = 0): ?RouteMatchInterface
    {
        $path = $request->getUri()->getPath();
        $path = $path === '' ? '/' : $path;
        $routeExp = '/' . str_replace('/', '\/', $this->pattern) . '/A';

        $matchResult = preg_match($routeExp, $path, $matches, 0, $offset);

        if (!$matchResult) {
            return null;
        }

        if (strlen($path) <= $offset + strlen($matches[0])) {
            $match = new RouteMatch();
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
            $match = $value->_match($request, $offset + strlen($matches[0]));
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

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name,
            'pattern' => $this->pattern,
            'params' => $this->params,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getParams(): array
    {
        return $this->params;
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
        return isset($this->children[$this->position]);
    }
}