# Router.

A very simple PRS-7 compatible router.

## How it works.
This Router doesn't try to be smart or clever. It doesn't have an opinion on what's it's routing. It doesn't re-write a route expression into RegExp, instead, it wants the expression to be expressed in a Regular Expression upfront. This allows greater control on what will be match against a URI.

This Router is constructed as a tree. This makes the Router that just a little bit faster as it doesn't need to go through a whole list (and every route definition) to find a match.

<svg viewBox="0 0 400 333" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <line x1="203.5" y1="38.5" x2="282.5" y2="120.5" id="Line-3" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="200.5" y1="38.5" x2="200.5" y2="120.5" id="Line-2" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="193.5" y1="44.5" x2="118.5" y2="120.5" id="Line" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="200.5" y1="120.5" x2="236.5" y2="202.5" id="Line-6" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="200.5" y1="120.5" x2="156.5" y2="202.5" id="Line-5" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="282.5" y1="124.5" x2="318.5" y2="202.5" id="Line-7" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="114.5" y1="127.5" x2="72.5" y2="202.5" id="Line-4" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="156.5" y1="207.5" x2="196.5" y2="284.5" id="Line-9" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <line x1="154.5" y1="207.5" x2="114.5" y2="284.5" id="Line-8" stroke="#D8D8D8" stroke-width="3" stroke-linecap="square"></line>
        <g id="Group-2" transform="translate(102.000000, 104.000000)">
            <circle id="Oval-Copy" fill="#FA6400" cx="16" cy="16" r="16"></circle>
            <text id="2" font-family="Helvetica" font-size="12" font-weight="normal" fill="#000000">
                <tspan x="13" y="20">2</tspan>
            </text>
        </g>
        <circle id="Oval-Copy-2" fill="#D8D8D8" cx="282" cy="120" r="16"></circle>
        <g id="Group-3" transform="translate(184.000000, 104.000000)">
            <circle id="Oval" fill="#44D7B6" cx="16" cy="16" r="16"></circle>
            <text id="3" font-family="Helvetica" font-size="12" font-weight="normal" fill="#000000">
                <tspan x="13" y="20">3</tspan>
            </text>
        </g>
        <circle id="Oval" fill="#D8D8D8" cx="236" cy="202" r="16"></circle>
        <circle id="Oval-Copy-3" fill="#D8D8D8" cx="318" cy="202" r="16"></circle>
        <circle id="Oval" fill="#D8D8D8" cx="72" cy="202" r="16"></circle>
        <g id="Group-4" transform="translate(138.000000, 186.000000)">
            <circle id="Oval-Copy-2" fill="#44D7B6" cx="16" cy="16" r="16"></circle>
            <text id="4" font-family="Helvetica" font-size="12" font-weight="normal" fill="#000000">
                <tspan x="13" y="20">4</tspan>
            </text>
        </g>
        <circle id="Oval" fill="#D8D8D8" cx="196" cy="284" r="16"></circle>
        <circle id="Oval-Copy-2" fill="#D8D8D8" cx="114" cy="284" r="16"></circle>
        <g id="Group" transform="translate(184.000000, 22.000000)">
            <circle id="Oval" fill="#44D7B6" cx="16" cy="16" r="16"></circle>
            <text id="1" font-family="Helvetica" font-size="12" font-weight="normal" fill="#000000">
                <tspan x="13" y="20">1</tspan>
            </text>
        </g>
    </g>
</svg>


## How to use it.
Define a Route, give it a name, an Expression and the Parameters you want returned when the Route is a match, then match it against a [PSR-7 Request](https://www.php-fig.org/psr/psr-7/)

```php
use Fizk\Router\Route;
use Laminas\Diactoros\Request;

$routes = new Route(
    'root',
    '/path/(?<id>\d+)',
    ['handler' => SomeHandler::class]
);

$request = new Request('http://this.is/path/1');

$match = $routes->match($request);

print_r($match->getAttributes())
// Will print
//[
//  'id' => '1'
//]

print_r($match->getParams())
// Will print
//[
//  'handler' => 'Namespace\\SomeHandler'
//]
```
As you can see, there is none of that `/path/:id` syntax, instead you need to write the full expression. If you want to capture the Attributes in the URI, you have to give them a name by using [Named Captures](https://php.watch/articles/php-regex-readability#named-captures)

### Nested routes.
Let's say we have a root path: `/path` and then we can have either numbers or letters as Attributes and we want different handlers/controller to run depending on which type Attribute is provided. We can express it like this:

```php
use Fizk\Router\Route;
use Laminas\Diactoros\Request;

$routes = (new Route('path', '/path', []))
    ->addRoute(new Route('letters', '/(?<id>[a-z]+)', ['controller' => SomeLetterController::class]))
    ->addRoute(new Route('number', '/(?<slug>\d+)', ['controller' => SomeNumberController::class]))
    ;

echo $routes->match(new Request('http://this.is/path/1'))->getParam('handler');
// Will print
// Namespace\\SomeNumberController

echo $routes->match(new Request('http://this.is/path/arg'))->getParam('handler');
// Will print
// Namespace\\SomeLetterController
```

Routes can be nested "infinitely" deep.

### The Router Class.
Defining routes with the `->addRoute(...)` syntax can be a bit verbose. This library provides a class than can take in configuration as an array and build the Router Tree, that way the router configuration is a little bit simpler to manage.

```php
// router.config.php
return [
    'base' => [
        'pattern' => '/',
        'options' => ['handler' => 'IndexHandler'],
    ],
    'albums' => [
        'pattern' => '/albums',
        'options' => ['handler' => 'AlbumsHandler'],
        'children' => [
            'album' => [
                'pattern' => '/(?<id>\d+)',
                'options' => ['handler' => 'AlbumHandler'],
            ],
        ]
    ],
];
```

```php
// index.php

$router = new Router(require './router.config.php');

$request = new Request('http://example.com/albums/1');
echo $router->match($request)->getParams('handler');

// Will print
// AlbumHandler
```

The array key will become the name of the Route. The required `pattern` and `options` keys will be passed to the Route instance. An optional `children` key can be defined, those routes will become children of the parent route.

Because this class has all the configuration inside of it, it can provide a method called `public function construct(string $path, ?array $arguments = []): ?string;` It can construct a URI based off the names you have given to the Routes. An example of this would be:
```php
$config = [
    'index' => [
        'pattern' => '/',
        'options' => ['handler' => 'IndexHandler'],
    ],
    'albums' => [
        'pattern' => '/albums',
        'options' => ['handler' => 'AlbumsHandler'],
        'children' => [
            'album' => [
                'pattern' => '/(?<id>\d+)',
                'options' => ['handler' => 'AlbumHandler'],
            ],
        ]
    ],
];

$router = new Router($config);
echo $router->construct('albums/album', ['id' => 1]);

// This will print
//  /albums/1
```


## Example
This examples uses `Fizk\Router` in conjunction with `Psr\Http\Message\ResponseInterface` and `Psr\Http\Message\ServerRequestInterface`. What is important to understand is that the Router is not going in inject any values from the URI into the `$responce` object or invoke the Controller/Handler. These are things you have to manage on your own.

The benefit of this is that the Router is not dependent on how Controllers/Handlers are implemented or which PSR standard it is using.

```php
use Fizk\Router\Route;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//Define Handlers/Controllers
class SomeNumberController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $data = $service->getById($id);
        return new JsonResponse($data);
    }
}

class SomeLetterController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $slug = $request->getAttribute('slug');
        $data = $service->getBySlug($slug);
        return new JsonResponse($slug);
    }
}

class ResourceNotFoundController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['message' => 'Resource Not Found'], 404);
    }
}

// Create a Request Object, pulling CGI values from global scope
$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST
    $_COOKIE,
    $_FILES
);
// Define an Emitter, which will set HTTP headers and body before delivering to client
$emitter = new SapiEmitter();

// Define Routes
$routes = (new Route('path', '/path', []))
    ->addRoute(new Route('letters', '/(?<id>[a-z]+)', ['controller' => new SomeLetterController()]))
    ->addRoute(new Route('number', '/(?<slug>\d+)', ['controller' => new SomeNumberController]))
    ;

// ...OR USE THE MORE COMPACT WAY OF DEFINING ROUTES
$routes = new Router([
    'path' => [
        'pattern' => '/path',
        'options' => []
        'children' => [
            'letters' => [
                'pattern' => '/(?<id>[a-z]+)',
                'options' => ['controller' => new SomeLetterController()]
            ],
            'numbers' => [
                'pattern' => '/(?<slug>\d+)',
                'options' => ['controller' => new SomeNumberController()]
            ],
        ]
    ]
]);

// Match Routes against Request Object
$match = $routes->match($request);

if ($match) {
    //Add attributes from URI to the Request Object
    foreach ($match->getAttributes() as $name => $value) {
        $request = $request->withAttribute($name, $value);
    }

    // Run the Handler/Controller
    $response = $match->getParam('controller')->handle($request);

    // Emit a Response back to client
    $emitter->emit($response);
} else {
    // Run the Error Handler/Controller
    $response = (new ResourceNotFoundController())->handle($request);

    // Emit a Response back to client
    $emitter->emit($response);
}
```