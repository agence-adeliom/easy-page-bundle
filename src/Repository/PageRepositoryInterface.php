<?php

namespace Adeliom\EasyPageBundle\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyPageBundle\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

interface PageRepositoryInterface
{
    /**
     * @return Page[]
     */
    public function getBySlug(string $slug): array;

    /**
     * Will search for pages to show in front depending on the arguments.
     * If slugs are defined, there's no problem in looking for nulled host or locale,
     * because slugs are unique, so it does not.
     *
     * @return Page[]
     */
    public function findFrontPages(array $slugs = [], ?string $host = null, ?string $locale = null): array;
}
