<?php

namespace Adeliom\EasyPageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension;

class EasyPageBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new EasyPageExtension();
    }
}
