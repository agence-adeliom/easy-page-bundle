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
        $this->cacheTtl     = $cacheConfig['ttl'];
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
            ->andWhere("page.action IS NOT NULL");

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
     * Will search for pages to show in front depending on the arguments.
     * If slugs are defined, there's no problem in looking for nulled host or locale,
     * because slugs are unique, so it does not.
     *
     * @param string|null $host
     * @param string|null $locale
     * @return Page[]
     */
    public function findFrontPages(array $slugs = [], $host = null, $locale = null)
    {
        $qb = $this->getPublishedQuery();

        // Will search differently if we're looking for homepage.
        $searchForHomepage = [] === $slugs;

        if ($searchForHomepage) {
            // If we are looking for homepage, let's get only the first one.
            $qb
                ->andWhere('page.template = :template')
                ->setParameter('template', "homepage")
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
        if ($searchForHomepage && $results !== []) {
            reset($results);
            $results = [$results[0]];
        }

        $resultsSortedBySlug = [];
        foreach ($results as $page) {
            $resultsSortedBySlug[$page->getSlug()] = $page;
        }

        $pages = $resultsSortedBySlug;

        if ($slugs !== []) {
            $pages = [];
            foreach ($slugs as $value) {
                if (!array_key_exists($value, $resultsSortedBySlug)) {
                    // Means at least one page in the tree is not enabled
                    return [];
                }

                $pages[$value] = $resultsSortedBySlug[$value];
            }
        }

        return $pages;
    }
}
