<?php

namespace Adeliom\EasyPageBundle\Event;

use Adeliom\EasyPageBundle\Entity\Page;
use Symfony\Contracts\EventDispatcher\Event;

class EasyPageBeforeTreeEvent extends Event
{
    /**
     * @var array<string, Page> $tree
     */
    public function __construct(protected array $tree = [])
    {
    }

    /**
     * @return array<string, Page>
     */
    public function getTree(): array
    {
        return $this->tree;
    }


    /**
     * @param array<string, Page> $tree
     */
    public function setTree(array $tree = [])
    {
        $this->tree = $tree;
    }
}
