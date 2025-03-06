<?php

namespace Adeliom\EasyPageBundle\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Iterator;

trait EasyPageTrait
{
    /**
     * @return Iterator<MenuItemInterface>
     */
    public function configPagesEntry(): iterable
    {
        $parameterBag = $this->container->get('parameter_bag');
        yield MenuItem::section('easy_page.pages');
        yield MenuItem::linkToCrud('easy_page.pages', 'fa fa-file-alt', $parameterBag->get('easy_page.page_class'));
    }
}
