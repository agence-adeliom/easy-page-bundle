<?php

namespace Adeliom\EasyPageBundle\EventListener;

use Adeliom\EasyPageBundle\Entity\Page;
use Adeliom\EasyPageBundle\Event\EasyPageBeforeTreeEvent;
use Adeliom\EasyPageBundle\Repository\PageRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Source;

class LayoutsListener implements EventSubscriberInterface
{
    public function __construct(
        /**
         * @readonly
         */
        private array $layouts,
        /**
         * @readonly
         */
        private Environment $twig,
        /**
         * @readonly
         */
        private PageRepository $pageRepository,
        /**
         * @readonly
         */
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['setRequestLayout', 33],
        ];
    }

    public function setRequestLayout(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Get the necessary informations to check them in layout configurations
        $path = $request->getPathInfo();
        $host = $request->getHost();

        // As a layout must be set, we force it to be empty if no layout is properly configured.
        // Then this will throw an exception, and the user will be warned of the "no-layout" config problem.
        $finalLayout = null;

        foreach ($this->layouts as $layoutConfig) {
            $match = false;

            // First check host
            if ($layoutConfig['host'] && $host === $layoutConfig['host']) {
                $match = true;
            }

            // Check pattern
            if ($layoutConfig['pattern'] && preg_match('~'.$layoutConfig['pattern'].'~', $path)) {
                $match = true;
            }

            if ($match) {
                $finalLayout = $layoutConfig;
                break;
            }
        }

        // If nothing matches, we take the first layout that has no "host" or "pattern" configuration.
        if (null === $finalLayout) {
            $layouts = $this->layouts;
            do {
                $finalLayout = array_shift($layouts);
                if ($finalLayout['host'] || $finalLayout['pattern']) {
                    $finalLayout = null;
                }
            } while (null === $finalLayout && count($layouts));
        }

        if (null === $finalLayout || !$this->twig->getLoader()->exists($finalLayout['resource'])) {
            $source = new Source('', $finalLayout['resource']);

            throw new LoaderError(sprintf('Unable to find template %s for layout %s. The "layout" parameter must be a valid twig view to be used as a layout.', $finalLayout['resource'], $finalLayout['name']), 0, $source);
        }

        /** @var Page[] $pages */
        $slugsArray = preg_split('#/#', $path, -1, PREG_SPLIT_NO_EMPTY);
        $pages = $this->pageRepository->findFrontPages($slugsArray, $event->getRequest()->getHost(), $event->getRequest()->getLocale());
        $tree = [];
        foreach ($pages as $page){
            $current = $page;
            do {
                $tree[$current->getSlug()] = $current;
                $current = $current->getParent();
            } while ($current);
        }

        $tree = ($this->eventDispatcher->dispatch(new EasyPageBeforeTreeEvent($tree)))->getTree();
        $page = last($tree);
        if (($page && $page->isHomepage()) || (count($tree) && ((is_countable($slugsArray) ? count($slugsArray) : 0) && count($tree) == (is_countable($slugsArray) ? count($slugsArray) : 0)))) {
            $event->getRequest()->attributes->set('_easy_page_pages', $tree);
        }

        $event->getRequest()->attributes->set('_easy_page_layout', $finalLayout);
    }
}
