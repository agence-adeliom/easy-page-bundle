<?php

namespace Adeliom\EasyPageBundle\EventListener;

use Adeliom\EasyPageBundle\Repository\PageRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /**
         * @readonly
         */
        private UrlGeneratorInterface $urlGenerator,
        /**
         * @readonly
         */
        private PageRepository $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPagesUrls($event->getUrlContainer());
    }

    public function registerPagesUrls(UrlContainerInterface $urls): void
    {
        $pages = $this->repository->getPublished();

        foreach ($pages as $page) {
            if ($page->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_page_index',
                            ['slugs' => $page->getTree()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                        $page->getUpdatedAt()
                    ),
                    'pages'
                );
            }
        }
    }
}
