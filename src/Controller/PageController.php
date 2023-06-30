<?php

namespace Adeliom\EasyPageBundle\Controller;

use Adeliom\EasyPageBundle\Entity\Page;
use Adeliom\EasyPageBundle\Event\EasyPageEvent;
use Adeliom\EasyPageBundle\Repository\PageRepository;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class PageController extends AbstractPageController
{
    public function __construct(
        /**
         * @readonly
         */
        private BreadcrumbCollection $breadcrumb,
        /**
         * @readonly
         */
        private Environment $twig,
        /**
         * @readonly
         */
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'easy_page.repository' => '?'.PageRepository::class,
            'event_dispatcher' => '?'.EventDispatcherInterface::class,
            'easy_seo.breadcrumb' => '?'.BreadcrumbCollection::class,
        ]);
    }

    public function index(Request $request, string $slugs = '', string $_locale = null): Response
    {
        if (preg_match('#/$#', $slugs)) {
            return $this->redirect($this->generateUrl('easy_page_index', ['slugs' => rtrim($slugs, '/')]));
        }

        $template = '@EasyPage/front/pages/default.html.twig';

        $request->setLocale($_locale ?: $request->getLocale());

        $slugsArray = preg_split('#/#', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        $pages = $this->getPages($request->attributes->get('_easy_page_pages'));

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        // If we have slugs and the current page is homepage,
        //  we redirect to homepage for "better" url and SEO management.
        // Example: if "/home" is a homepage, "/home" url is redirected to "/".
        if ($slugs && $currentPage->isHomepage()) {
            $params = ['slugs' => ''];

            return $this->redirect(rtrim($this->generateUrl('easy_page_index', $params), '/') . '/');
        }

        if ($currentPage->getTemplate() && $this->twig->getLoader()->exists('@EasyPage/front/pages/'.$currentPage->getTemplate().'.html.twig')) {
            $template = '@EasyPage/front/pages/'.$currentPage->getTemplate().'.html.twig';
        }

        if ($currentPage->getTemplate() && $this->twig->getLoader()->exists('pages/'.$currentPage->getTemplate().'.html.twig')) {
            $template = 'pages/'.$currentPage->getTemplate().'.html.twig';
        }

        if (!$this->twig->getLoader()->exists($template)) {
            throw new \Exception('Template not found '.$template);
        }

        $this->breadcrumb->addRouteItem('homepage', ['route' => 'easy_page_index']);

        if (!$currentPage->isHomepage()) {
            foreach ($pages as $page) {
                $this->breadcrumb->addRouteItem($page->getName(), ['route' => 'easy_page_index', 'params' => ['slugs' => $page->getTree()]], $page);
            }
        }

        $args = [
            'pages' => $pages,
            'page' => $currentPage,
            'breadcrumb' => $this->breadcrumb,
        ] + $request->attributes->all();

        $event = new EasyPageEvent($currentPage, $args, $template);
        /**
         * @var EasyPageEvent $result;
         */
        $result = $this->eventDispatcher->dispatch($event, EasyPageEvent::NAME);

        if (!empty($args['page']->getAction())) {
            $args = $result->getArgs();

            return $this->forward($args['page']->getAction(), $args, $request->query->all());
        }

        return $this->render($result->getTemplate(), $result->getArgs());
    }

    /**
     * Retrieves the page list based on slugs.
     * Also checks the hierarchy of the different pages.
     *
     * @param Page[]|null $pages
     *
     * @return Page[]
     */
    protected function getPages(?array $pages = []): array
    {
        if (empty($pages)) {
            throw $this->createNotFoundException('Page not found');
        }

        return $pages;
    }

    /**
     * Retrieves the current page based on page list and entered slugs.
     *
     * @param Page[]   $pages
     * @param string[] $slugsArray
     */
    protected function getCurrentPage(array $pages, array $slugsArray): Page
    {
        return count($pages) === count($slugsArray) ? $this->getFinalTreeElement($slugsArray, $pages) : current($pages);
    }
}
