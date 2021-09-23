<?php

namespace Adeliom\EasyPageBundle\Routing;


use Adeliom\EasyPageBundle\Repository\PageRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PageLoader extends Loader
{
    private $isLoaded = false;

    private $controller;
    private $entity;
    private $repository;


    public function __construct(string $controller, string $entity, PageRepository $repository, string $env = null)
    {
        parent::__construct($env);

        $this->controller = $controller;
        $this->entity = $entity;
        $this->repository = $repository;

    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
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
        $path = '/{slugs}';
        $defaults = [
            '_controller' => $this->controller . '::index',
            'slugs' => '',
        ];
        $requirements = [
            'slugs' => "([a-zA-Z0-9_-]+\/?)*",
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_page_pages')");

        // add the new route to the route collection
        $routeName = 'easy_page_index';
        $routes->add($routeName, $route, -100);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null)
    {
        return 'easy_page' === $type;
    }
}
