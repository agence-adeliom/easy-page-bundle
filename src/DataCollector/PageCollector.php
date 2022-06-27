<?php

namespace Adeliom\EasyPageBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PageCollector extends AbstractDataCollector
{
    protected ContainerBagInterface $parameterBag;

    public function __construct(ContainerBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $layout = $request->get("_easy_page_layout");
        $layouts = $this->parameterBag->get("easy_page.layouts");

        $this->data = [
            "layout" => $layout,
            "layouts" => $layouts
        ];
    }

    /**
     * @return array
     */
    public function getLayout(): array
    {
        return $this->data["layout"] ?: [];
    }

    /**
     * @return array
     */
    public function getLayouts(): array
    {
        return $this->data["layouts"] ?: [];
    }

    public function getName(): string
    {
        return self::class;
    }
}
