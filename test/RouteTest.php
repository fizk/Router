<?php

namespace Fizk\Router;

use Fizk\Router\Route;
use Fizk\Router\RouteMatch;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Request;
use DomainException;


class RouteTest extends TestCase
{
    public function testSuccessOne()
    {
        //GIVEN
        $routes = (
            new Route('root', '')
        )
        ->addRoute(
            new Route('one', '/hundur', ['controller' => 'ControllerStuff'])
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
        $request = new Request('http://this.is/hundur');
        $match = $routes->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals([], $match->getAttributes());
        $this->assertEquals([
            'controller' => 'ControllerStuff'
        ], $match->getParams());
    }

    public function testSuccessTwo()
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
        $request = new Request('http://this.is/stuff');
        $match = $routes->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals([], $match->getAttributes());
        $this->assertEquals([
            'controller' => 'ControllerTwo'
        ], $match->getParams());
    }

    public function testSuccessThree()
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
        $request = new Request('http://this.is/stuff/1');
        $match = $routes->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals([
            'id' => 1,
        ], $match->getAttributes());
        $this->assertEquals([
            'controller' => 'ControllerTwoOne'
        ], $match->getParams());
    }

    public function testSuccessFour()
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
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals([
            'id' => 1,
            'id2' => 3,
        ], $match->getAttributes());
        $this->assertEquals([
            'controller' => 'ControllerTwoOneOne'
        ], $match->getParams());
    }

    public function testSuccessFourFail()
    {
        $this->expectException(DomainException::class);

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
        $request = new Request('http://this.is/stuff/1/string');
        $match = $routes->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
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
        $this->expectException(DomainException::class);

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
        // $this->assertNull($match);
    }
}