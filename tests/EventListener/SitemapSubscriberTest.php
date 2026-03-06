<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\EventListener;

use Adeliom\EasyPageBundle\Entity\Page;
use Adeliom\EasyPageBundle\EventListener\SitemapSubscriber;
use Adeliom\EasyPageBundle\Repository\PageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(\Adeliom\EasyPageBundle\EventListener\SitemapSubscriber::class)]
final class SitemapSubscriberTest extends TestCase
{
    public function testSubscriberRegistersPublishedSitemapPages(): void
    {
        $page = new Page();
        $page->setName('Landing');
        $page->setSlug('landing');
        $page->getSEO()->sitemap = true;

        $repository = $this->createMock(PageRepository::class);
        $repository->method('getPublished')->willReturn([$page]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with('easy_page_index', ['slugs' => 'landing'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/landing');

        $urls = $this->createMock(UrlContainerInterface::class);
        $urls->expects(self::once())
            ->method('addUrl')
            ->with(self::callback(static fn ($url): bool => 'https://example.com/landing' === $url->getLoc()), 'pages');

        $event = new SitemapPopulateEvent($urls, $urlGenerator);

        $subscriber = new SitemapSubscriber($urlGenerator, $repository, true);

        self::assertSame([SitemapPopulateEvent::class => 'populate'], SitemapSubscriber::getSubscribedEvents());

        $subscriber->populate($event);
    }

    public function testSubscriberSkipsRegistrationWhenDisabled(): void
    {
        $repository = $this->createMock(PageRepository::class);
        $repository->expects(self::never())->method('getPublished');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $event = $this->createMock(SitemapPopulateEvent::class);

        (new SitemapSubscriber($urlGenerator, $repository, false))->populate($event);

        self::assertTrue(true);
    }
}
