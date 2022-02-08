<?php

namespace Fizk\Router;

use Fizk\Router\Route;
use Fizk\Router\RouteMath;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Request;

class RouteTest extends TestCase
{
    public function testSuccess()
    {
        //GIVEN
        $routes = (
            new Route('root', '')
        )
        ->addRoute(
            new Route('one', '/hundur', [])
        )
        ->addRoute(
            (new Route('two', '/stuff', ['controller' => 'ControllerTwo']))
                ->addRoute(
                    (new Route('two.one', '/(?<id>\d+)', ['controller' => 'ControllerTwoOne']))
                        ->addRoute(new Route('two.one.one', '/(?<id2>\d+)', ['controller' => 'ControllerTwoOneOne']))
                )
                ->addRoute(
                    (new Route('two.two', '/(?<id>[a-z]+)', ['controller' => 'ControllerTwoTwo']))
                        ->addRoute(new Route('two.two.one', '/(?<id2>\d+)', ['controller' => 'ControllerTwoTwoOne']))
                )
        );

        //WHEN
        $request = new Request('http://this.is/stuff/1/3');
        $match = $routes->match($request);

        //THEN
        $this->assertInstanceOf(RouteMath::class, $match);
        $this->assertEquals([
            'id' => 1,
            'id2' => 3,
        ], $match->getAttributes());
        $this->assertEquals([
            'controller' => 'ControllerTwoOneOne'
        ], $match->getParams());
    }

    public function testFail()
    {
        //GIVEN
        $routes = (
            new Route('root', '')
        )
        ->addRoute(
            new Route('one', '/hundur', [])
        )
        ->addRoute(
            (new Route('two', '/stuff', ['controller' => 'ControllerTwo']))
                ->addRoute(
                    (new Route('two.one', '/(?<id>\d+)', ['controller' => 'ControllerTwoOne']))
                        ->addRoute(new Route('two.one.one', '/(?<id2>\d+)', ['controller' => 'ControllerTwoOneOne']))
                )
                ->addRoute(
                    (new Route('two.two', '/(?<id>[a-z]+)', ['controller' => 'ControllerTwoTwo']))
                        ->addRoute(new Route('two.two.one', '/(?<id2>\d+)', ['controller' => 'ControllerTwoTwoOne']))
                )
        );

        //WHEN
        $request = new Request('http://this.is/doesn/not/work');
        $match = $routes->match($request);

        //THEN
        $this->assertNull($match);
    }
}