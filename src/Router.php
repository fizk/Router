<?php

namespace Fizk\Router;

use Psr\Http\Message\RequestInterface;
use Fizk\Router\RouteMatchInterface;
use InvalidArgumentException;

class Router implements RouterInterface
{
    private ?Route $root = null;
    private array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->root = new Route('', '', []);
        $this->build($config, $this->root);
    }

    private function build(array $config, Route &$parent)
    {
        foreach ($config as $key => $value) {
            if (
                array_key_exists('pattern', $value) &&
                array_key_exists('options', $value)
            ) {
                $child = new Route($key, $value['pattern'], $value['options']);
                if (array_key_exists('children', $value)) {
                    $this->build($value['children'], $child);
                }
                $parent->addRoute($child);
            }
        }
    }

    public function match(RequestInterface $request): ?RouteMatchInterface
    {
        return $this->root->match($request);
    }

    public function construct(string $path, ?array $arguments = []): ?string
    {
        $uri = '';
        $currentConfig = $this->config;
        $namePath = explode('/', $path);
        foreach($namePath as $path) {
            if (is_array($currentConfig)) {
                if (!array_key_exists($path, $currentConfig)) {
                    throw new InvalidArgumentException("Route $path does not exist");
                }
                $uri .= $currentConfig[$path]['pattern'];
                $currentConfig = array_key_exists('children', $currentConfig[$path])
                    ? $currentConfig[$path]['children']
                    : null;
            }
        }

        foreach($arguments as $key => $value) {
            $uri = preg_replace('/\(\?<'. $key .'>.*?\)/', $value, $uri);
        }

        return $uri;
    }
}