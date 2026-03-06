<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\DependencyInjection;

use Adeliom\EasyPageBundle\Controller\PageController;
use Adeliom\EasyPageBundle\DependencyInjection\Configuration;
use Adeliom\EasyPageBundle\Repository\PageRepository;
use Adeliom\EasyPageBundle\Tests\Fixtures\Controller\TestPageController;
use Adeliom\EasyPageBundle\Tests\Fixtures\Entity\TestPage;
use Adeliom\EasyPageBundle\Tests\Fixtures\Repository\TestPageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(\Adeliom\EasyPageBundle\DependencyInjection\Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testConfigurationAcceptsPageClassAndAppliesDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'page_class' => TestPage::class,
        ]]);

        self::assertSame(TestPage::class, $config['page_class']);
        self::assertSame(PageRepository::class, $config['page_repository']);
        self::assertSame(PageController::class, $config['page_controller']);
        self::assertFalse($config['trailing_slash']);
        self::assertTrue($config['sitemap']);
        self::assertFalse($config['cache']['enabled']);
        self::assertSame(300, $config['cache']['ttl']);
        self::assertSame('@EasyPage/default_layout.html.twig', $config['layouts']['front']['resource']);
    }

    public function testConfigurationAcceptsCustomValues(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'page_class' => TestPage::class,
            'page_repository' => TestPageRepository::class,
            'page_controller' => TestPageController::class,
            'trailing_slash' => '1',
            'sitemap' => false,
            'cache' => [
                'enabled' => true,
                'ttl' => 900,
            ],
            'layouts' => [
                'landing' => [
                    'resource' => '@App/landing.html.twig',
                    'assets_css' => ['landing.css'],
                    'pattern' => '/landing',
                ],
            ],
        ]]);

        self::assertSame(TestPageRepository::class, $config['page_repository']);
        self::assertSame(TestPageController::class, $config['page_controller']);
        self::assertTrue($config['trailing_slash']);
        self::assertFalse($config['sitemap']);
        self::assertSame(['landing.css'], $config['layouts']['landing']['assets_css']);
        self::assertSame('/landing', $config['layouts']['landing']['pattern']);
    }

    public function testConfigurationRejectsInvalidPageClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'page_class' => \stdClass::class,
        ]]);
    }
}
