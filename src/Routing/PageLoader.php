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
        /**
         * @readonly
         */
        private string $controller,
        /**
         * @readonly
         */
        private string $entity,
        /**
         * @readonly
         */
        private PageRepository $repository,
        /**
         * @readonly
         */
        private bool $trailingSlash,
        string $env = null
    ) {
        parent::__construct($env);
    }

    /**
     * @return mixed
     *
     * @throws \Exception If something went wrong
     */
    public function load($resource, string $type = null)
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_page" loader twice');
        }

        $routes = new RouteCollection();

        /*foreach ($this->repository->getAllCustom() as $pageEntity){
            $path = sprintf('/%s', $pageEntity->getTree());
            $defaults = [
                '_controller' => $pageEntity->getAction(),
                'page' => $pageEntity->getId()
            ];
            $requirements = [];
            $route = new Route($path, $defaults, $requirements);
            $routeName = 'easy_page_custom__' . mb_strtolower((new AsciiSlugger())->slug($pageEntity->getTree())->toString());
            $routes->add($routeName, $route, -90);
        }*/

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

    public function supports($resource, string $type = null): bool
    {
        return 'easy_page' === $type;
    }
}
