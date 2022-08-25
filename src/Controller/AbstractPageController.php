<?php

namespace Adeliom\EasyPageBundle\Controller;

use Adeliom\EasyPageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractPageController extends AbstractController
{
    /**
     * Slugs HAVE TO be ordered exactly as in the request.
     * This method will check that, in $elements, we have the same keys as in $slugs,
     * and that the hierarchy is correct.
     * This also prevents things like /children/parent to work,
     * as it should be /parent/children.
     *
     * @param Page[] $elements
     * @return Page
     */
    protected function getFinalTreeElement(array $slugs, array $elements)
    {
        // Will check that slugs and elements match
        $slugsElements = array_keys($elements);
        $sortedSlugs   = $slugs;
        sort($sortedSlugs);
        sort($slugsElements);

        if ($sortedSlugs !== $slugsElements || $slugs === [] || count($slugs) !== count($elements)) {
            throw $this->createNotFoundException();
        }

        /** @var Page $element */
        $element = null;
        /** @var Page $previousElement */
        $previousElement = null;

        foreach ($slugs as $slug) {
            $element = $elements[$slug] ?? null;
            $match   = false;
            if ($element !== null) {
                // Only for the first iteration
                $match = $previousElement
                    ? $element->getParent() && $previousElement->getSlug() === $element->getParent()->getSlug()
                    : true;

                $previousElement = $element;
            }

            if (!$match) {
                throw $this->createNotFoundException((new \ReflectionClass($element))->getShortName() . ' hierarchy not found.');
            }
        }

        return $element;
    }
}
