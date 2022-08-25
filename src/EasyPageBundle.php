<?php

namespace Adeliom\EasyPageBundle;

use Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyPageBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EasyPageExtension();
    }
}
