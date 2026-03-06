<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\DependencyInjection;

use Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension;
use Adeliom\EasyPageBundle\Tests\Fixtures\Controller\TestPageController;
use Adeliom\EasyPageBundle\Tests\Fixtures\Entity\TestPage;
use Adeliom\EasyPageBundle\Tests\Fixtures\Repository\TestPageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(\Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension::class)]
final class EasyPageExtensionTest extends TestCase
{
    public function testExtensionLoadsParametersAndServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyPageExtension();

        $extension->load([[
            'page_class' => TestPage::class,
            'page_repository' => TestPageRepository::class,
            'page_controller' => TestPageController::class,
            'trailing_slash' => true,
            'cache' => [
                'enabled' => true,
                'ttl' => 1200,
            ],
            'layouts' => [
                'landing' => [
                    'resource' => '@App/landing.html.twig',
                    'assets_js' => ['landing.js'],
                ],
            ],
        ]], $container);

        self::assertSame('easy_page', $extension->getAlias());
        self::assertSame(TestPage::class, $container->getParameter('easy_page.page_class'));
        self::assertSame(TestPageRepository::class, $container->getParameter('easy_page.page_repository'));
        self::assertSame(TestPageController::class, $container->getParameter('easy_page.page_controller'));
        self::assertTrue($container->getParameter('easy_page.trailing_slash'));
        self::assertSame(['enabled' => true, 'ttl' => 1200], $container->getParameter('easy_page.cache'));
        self::assertSame([
            'assets_css' => [],
            'assets_js' => ['landing.js'],
            'assets_webpack' => [],
            'host' => '',
            'name' => 'landing',
            'pattern' => '',
            'resource' => '@App/landing.html.twig',
        ], $container->getParameter('easy_page.layouts')['landing']);
        self::assertTrue($container->hasDefinition('easy_page.route_loader'));
        self::assertTrue($container->hasDefinition('easy_page.repository'));
        self::assertTrue($container->hasDefinition('easy_page.sitemap.page_subscriber'));
    }
}
