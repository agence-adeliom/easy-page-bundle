<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests;

use Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension;
use Adeliom\EasyPageBundle\EasyPageBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyPageBundle\EasyPageBundle::class)]
final class EasyPageBundleTest extends TestCase
{
    public function testBundleReturnsExpectedContainerExtension(): void
    {
        $bundle = new EasyPageBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(EasyPageExtension::class, $extension);
        self::assertSame('easy_page', $extension?->getAlias());
    }
}
