<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\DataCollector;

use Adeliom\EasyPageBundle\DataCollector\PageCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(\Adeliom\EasyPageBundle\DataCollector\PageCollector::class)]
final class PageCollectorTest extends TestCase
{
    public function testCollectStoresResolvedLayoutData(): void
    {
        $parameterBag = $this->createMock(ContainerBagInterface::class);
        $parameterBag->expects(self::once())
            ->method('get')
            ->with('easy_page.layouts')
            ->willReturn([
                ['name' => 'default', 'resource' => '@EasyPage/default_layout.html.twig'],
            ]);

        $collector = new PageCollector($parameterBag);
        $request = new Request();
        $request->attributes->set('_easy_page_layout', ['name' => 'default']);

        $collector->collect($request, new Response());

        self::assertSame(['name' => 'default'], $collector->getLayout());
        self::assertSame([
            ['name' => 'default', 'resource' => '@EasyPage/default_layout.html.twig'],
        ], $collector->getLayouts());
        self::assertSame(PageCollector::class, $collector->getName());
    }

    public function testCollectorReturnsEmptyArraysWhenNothingWasCollected(): void
    {
        $parameterBag = $this->createMock(ContainerBagInterface::class);
        $parameterBag->method('get')->with('easy_page.layouts')->willReturn(null);

        $collector = new PageCollector($parameterBag);
        $collector->collect(new Request(), new Response());

        self::assertSame([], $collector->getLayout());
        self::assertSame([], $collector->getLayouts());
    }
}
