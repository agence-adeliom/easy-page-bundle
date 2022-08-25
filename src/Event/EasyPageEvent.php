<?php

namespace Adeliom\EasyPageBundle\Event;

use Adeliom\EasyPageBundle\Entity\Page;
use Symfony\Contracts\EventDispatcher\Event;

class EasyPageEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = "easypage.before_render";


    public function __construct(protected Page $page, protected $args, protected $template)
    {
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
