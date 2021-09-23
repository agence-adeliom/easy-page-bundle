<?php

namespace Adeliom\EasyPageBundle\EventListener;

use Adeliom\EasyPageBundle\Repository\PageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PageRepository
     */
    private $repository;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param PageRepository    $repository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, PageRepository $repository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPagesUrls($event->getUrlContainer());
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerPagesUrls(UrlContainerInterface $urls): void
    {
        $pages = $this->repository->getPublished();

        foreach ($pages as $page) {
            if($page->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_page_index',
                            ['slugs' => $page->getTree()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    'pages'
                );
            }
        }
    }
}
