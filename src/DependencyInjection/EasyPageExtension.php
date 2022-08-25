<?php

namespace Adeliom\EasyPageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyPageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        foreach ($config['layouts'] as $name => $layout) {
            $config['layouts'][$name] = array_merge([
                'name'       => $name,
                'assets_css' => [],
                'assets_js'  => [],
                'assets_webpack'  => [],
                'host'       => '',
                'pattern'    => '',
            ], $layout);
            ksort($config['layouts'][$name]);
        }

        foreach ($config as $key => $value) {
            $container->setParameter('easy_page.' . $key, $value);
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }


    public function getAlias(): string
    {
        return 'easy_page';
    }
}
