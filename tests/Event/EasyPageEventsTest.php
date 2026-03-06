<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\Event;

use Adeliom\EasyPageBundle\Entity\Page;
use Adeliom\EasyPageBundle\Event\EasyPageBeforeTreeEvent;
use Adeliom\EasyPageBundle\Event\EasyPageEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyPageBundle\Event\EasyPageBeforeTreeEvent::class)]
#[CoversClass(\Adeliom\EasyPageBundle\Event\EasyPageEvent::class)]
final class EasyPageEventsTest extends TestCase
{
    public function testEventsExposeMutablePayload(): void
    {
        $page = new Page();
        $page->setName('Landing');
        $treeEvent = new EasyPageBeforeTreeEvent(['landing' => $page]);

        self::assertSame(['landing' => $page], $treeEvent->getTree());

        $treeEvent->setTree([]);
        self::assertSame([], $treeEvent->getTree());

        $renderEvent = new EasyPageEvent($page, ['foo' => 'bar'], '@EasyPage/front/pages/default.html.twig');
        self::assertSame(EasyPageEvent::NAME, 'easypage.before_render');
        self::assertSame($page, $renderEvent->getPage());
        self::assertSame(['foo' => 'bar'], $renderEvent->getArgs());
        self::assertSame('@EasyPage/front/pages/default.html.twig', $renderEvent->getTemplate());

        $renderEvent->setArgs(['baz' => 'qux']);
        $renderEvent->setTemplate('pages/custom.html.twig');

        self::assertSame(['baz' => 'qux'], $renderEvent->getArgs());
        self::assertSame('pages/custom.html.twig', $renderEvent->getTemplate());
    }
}
