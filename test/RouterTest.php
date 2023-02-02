<?php

namespace Fizk\Router;

use Fizk\Router\RouteMatch;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Request;
use InvalidArgumentException;

class RouterTest extends TestCase
{
    public function testThreeLevelChildIsMatched()
    {
        $config = [
            'one' => [
                'pattern' => '/hundur',
                'options' => [],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $request = new Request('http://this.is/stuff/one/3?param=4');
        $match = $router->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals([
            'id' => 'one',
            'id2' => 3,
        ], $match->getAttributes());
        $this->assertEquals([
            'handler' => 'ControllerTwoTwoOne'
        ], $match->getParams());
    }

    public function testRootDomainPatternHasToStartWithASlash()
    {
        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $requestWithoutSlash = new Request('http://this.is');
        $matchWithoutSlash = $router->match($requestWithoutSlash);

        $requestWithSlash = new Request('http://this.is/');
        $matchWithSlash = $router->match($requestWithSlash);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $matchWithoutSlash);
        $this->assertEquals(['handler' => 'ControllerOne'], $matchWithoutSlash->getParams());

        $this->assertInstanceOf(RouteMatch::class, $matchWithSlash);
        $this->assertEquals(['handler' => 'ControllerOne'], $matchWithSlash->getParams());
    }

    public function testRootDomainDoesntOverwriteOtherPatterns()
    {
        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $request = new Request('http://this.is/stuff');
        $match = $router->match($request);

        //THEN
        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertEquals(['handler' => 'ControllerTwo'], $match->getParams());
    }

    public function testConstructUrl()
    {
        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $expected = '/stuff/param';
        $actual = $router->construct('two/two.two', ['id' => 'param']);

        //THEN
        $this->assertEquals($expected, $actual);
    }

    public function testConstructUrlMultiParams()
    {
        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $expected = '/stuff/param/param2';
        $actual = $router->construct('two/two.two/two.two.one', [
            'id' => 'param',
            'id2' => 'param2',
        ]);

        //THEN
        $this->assertEquals($expected, $actual);
    }

    public function testConstructUrlParamsDontMatch()
    {
        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $expected = '/stuff/(?<id>[a-z]+)';
        $actual = $router->construct('two/two.two', ['name' => 'param']);

        //THEN
        $this->assertEquals($expected, $actual);
    }

    public function testConstructUrlNotFound()
    {
        $this->expectException(InvalidArgumentException::class);

        // GIVEN
        $config = [
            'one' => [
                'pattern' => '/',
                'options' => ['handler' => 'ControllerOne'],
            ],
            'two' => [
                'pattern' => '/stuff',
                'options' => ['handler' => 'ControllerTwo'],
                'children' => [
                    'two.one' => [
                        'pattern' => '/(?<id>\d+)',
                        'options' => ['handler' => 'ControllerTwoOne'],
                    ],
                    'two.two' => [
                        'pattern' => '/(?<id>[a-z]+)',
                        'options' => ['handler' => 'ControllerTwoTwo'],
                        'children' => [
                            'two.two.one' => [
                                'pattern' => '/(?<id2>\d+)',
                                'options' => ['handler' => 'ControllerTwoTwoOne'],
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $router = new Router($config);

        //WHEN
        $expected = '/stuff/param';
        $actual = $router->construct('two/two.not-found', ['id' => 'param']);

        //THEN
        $this->assertEquals($expected, $actual);
    }
}