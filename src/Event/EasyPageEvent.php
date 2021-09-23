<?php

namespace Adeliom\EasyPageBundle\Event;

use Adeliom\EasyPageBundle\Entity\Page;
use Symfony\Contracts\EventDispatcher\Event;

class EasyPageEvent extends Event
{

    public const NAME = "easypage.before_render";

    protected $page;
    protected $args;
    protected $template;

    public function __construct(Page $page, $args, $template)
    {
        $this->page = $page;
        $this->args = $args;
        $this->template = $template;
    }

    /**
     * @return Page
     */
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
