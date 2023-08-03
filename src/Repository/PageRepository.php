<?php

namespace Adeliom\EasyPageBundle\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyPageBundle\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

class PageRepository extends ServiceEntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    public function setConfig(array $cacheConfig)
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.state = :state')
            ->andWhere('page.publishDate < :publishDate')
        ;

        $orModule = $qb->expr()->orx();
        $orModule->add($qb->expr()->gt('page.unpublishDate', ':unpublishDate'));
        $orModule->add($qb->expr()->isNull('page.unpublishDate'));

        $qb->andWhere($orModule);

        $qb->setParameter('state', ThreeStateStatusEnum::PUBLISHED());
        $qb->setParameter('publishDate', new \DateTime());
        $qb->setParameter('unpublishDate', new \DateTime());

        return $qb;
    }

    /**
     * @return Page[]
     */
    public function getPublished()
    {
        $qb = $this->getPublishedQuery();

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return Page[]
     */
    public function getAllCustom()
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere("page.action != ''")
            ->andWhere('page.action IS NOT NULL');

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return Page[]
     */
    public function getByAction(string $action)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('page.action = :action')
            ->setParameter('action', $action);

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return Page[]
     */
    public function getByTemplate(string $template)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('page.template = :template')
            ->setParameter('template', $template);

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return Page[]
     */
    public function getBySlug(string $slug): array
    {
        $qb = $this->getPublishedQuery()
            ->andWhere('page.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * Will search for pages to show in front depending on the arguments.
     * If slugs are defined, there's no problem in looking for nulled host or locale,
     * because slugs are unique, so it does not.
     *
     * @return Page[]
     */
    public function findFrontPages(array $slugs = [], ?string $host = null, ?string $locale = null): array
    {
        $qb = $this->getPublishedQuery();
        $allItemsPublished = true;

        // Will search differently if we're looking for homepage.
        $searchForHomepage = [] === $slugs;

        $useConstructedTree = false;
        $constructedTree = [];

        foreach ($this->getBySlug(last($slugs)) as $item) {
            $hasNonPageElement = false;
            $allItemsPublished = true;

            $itemSlug = method_exists($item, 'getPageSlug') ? $item->getPageSlug() : $item->getSlug();
            $tempConstructedTree[$itemSlug] = $item;

            while ($item->getParent()) {
                $item = $item->getParent();

                if (!$item instanceof Page) {
                    if (method_exists($item, 'getState')) {
                        // If getState exists, checks if item is published to return (or not) a 404
                        if ($item->getState() !== ThreeStateStatusEnum::PUBLISHED()->getValue()) {
                            $allItemsPublished = false;
                        }
                    }

                    if (!$hasNonPageElement) {
                        // Set to true to know whether the tree contains non-page items
                        $hasNonPageElement = true;
                    }
                }


                $itemSlug = method_exists($item, 'getPageSlug') ? $item->getPageSlug() : $item->getSlug();
                $tempConstructedTree = array_merge([$itemSlug => $item], $tempConstructedTree);
            }

			$constructedKeys = array_keys($tempConstructedTree);

			if($hasNonPageElement){
				if($constructedKeys !== $slugs){
					return [];
				}
			}

            if ($constructedKeys === $slugs) {
                $useConstructedTree = true;
                $constructedTree = $tempConstructedTree;
                break;
            }
        }

        if ($useConstructedTree) {
            // If all items in tree (non-pages) are not published, 404
            if (!$allItemsPublished) {
                return [];
            }

            $resultsSortedBySlug = $constructedTree;
            $pages = $constructedTree;
        } else {
            if ($searchForHomepage) {
                // If we are looking for homepage, let's get only the first one.
                $qb
                    ->andWhere('page.template = :template')
                    ->setParameter('template', 'homepage')
                    ->setMaxResults(1)
                ;
            } elseif (1 === count($slugs)) {
                $qb
                    ->andWhere('page.slug = :slug')
                    ->setParameter('slug', reset($slugs))
                    ->setMaxResults(1)
                ;
            } else {
                $qb
                    ->andWhere('page.slug IN ( :slugs )')
                    ->setParameter('slugs', $slugs)
                ;
            }

//        $localeWhere = 'page.locale IS NULL';
//        if (null !== $locale) {
//            $localeWhere .= ' OR page.locale = :locale';
//            $qb->setParameter('locale', $locale);
//            $qb->addOrderBy('page.locale', 'asc');
//        }
//        $qb->andWhere($localeWhere);

            /** @var Page[] $results */
            $results = $qb->getQuery()
                ->useResultCache($this->cacheEnabled, $this->cacheTtl)
                ->getResult()
            ;

            if ([] === $results) {
                return $results;
            }

            // If we're looking for a homepage, only get the first result (matching more properties).
            if ($searchForHomepage && [] !== $results) {
                reset($results);
                $results = [$results[0]];
            }

            $resultsSortedBySlug = [];
            foreach ($results as $page) {
                $resultsSortedBySlug[$page->getSlug()] = $page;
            }

            $pages = $resultsSortedBySlug;
        }

        if ([] !== $slugs) {
            $pages = [];
            foreach ($slugs as $value) {
                if (!array_key_exists($value, $resultsSortedBySlug)) {
                    if (array_key_exists($value, $constructedTree)) {
                        $resultsSortedBySlug[$value] = $constructedTree[$value];
                    } else {
                        // Means at least one page in the tree is not enabled
                        return [];
                    }
                }

                $pages[$value] = $resultsSortedBySlug[$value];
            }
        }

        return $pages;
    }
}
