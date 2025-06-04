<?php

namespace Adeliom\EasyPageBundle\Routing;

use Adeliom\EasyPageBundle\Repository\PageRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PageLoader extends Loader
{
    private bool $isLoaded = false;

    public function __construct(
        private readonly string $controller,
        private readonly bool $trailingSlash,
        string $env = null
    ) {
        parent::__construct($env);
    }

    /**
     * @throws \Exception If something went wrong
     * @return RouteCollection
     */
    public function load(mixed $resource, ?string $type = null): mixed
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_page" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = '/{slugs}' . ($this->trailingSlash ? '/' : '');
        $defaults = [
            '_controller' => $this->controller . '::index',
            'slugs' => '',
        ];
        $requirements = [
            'slugs' => "([a-zA-Z0-9_-]+\/?)*" . ($this->trailingSlash ? '|^$' : ''), // if trailing slash, then also allow for empty path (homepage)
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_page_pages')");

        // add the new route to the route collection
        $routeName = 'easy_page_index';
        $routes->add($routeName, $route, -100);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'easy_page' === $type;
    }
}
