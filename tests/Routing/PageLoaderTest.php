<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\Routing;

use Adeliom\EasyPageBundle\Routing\PageLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(\Adeliom\EasyPageBundle\Routing\PageLoader::class)]
final class PageLoaderTest extends TestCase
{
    public function testLoaderBuildsRouteWithTrailingSlash(): void
    {
        $loader = new PageLoader('App\\Controller\\PageController', true);

        self::assertTrue($loader->supports(null, 'easy_page'));

        $routes = $loader->load(null, 'easy_page');

        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertTrue($routes->get('easy_page_index')->getPath() === '/{slugs}/');
        self::assertSame("([a-zA-Z0-9_-]+\\/?)*|^", $routes->get('easy_page_index')->getRequirement('slugs'));
        self::assertSame('App\\Controller\\PageController::index', $routes->get('easy_page_index')->getDefault('_controller'));
    }

    public function testLoaderCannotBeLoadedTwice(): void
    {
        $loader = new PageLoader('App\\Controller\\PageController', false);
        $loader->load(null, 'easy_page');

        $this->expectException(\RuntimeException::class);

        $loader->load(null, 'easy_page');
    }
}
