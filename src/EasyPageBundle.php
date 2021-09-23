<?php

namespace Adeliom\EasyPageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Adeliom\EasyPageBundle\DependencyInjection\EasyPageExtension;

class EasyPageBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EasyPageExtension();
    }
}
