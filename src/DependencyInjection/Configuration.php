<?php

namespace Adeliom\EasyPageBundle\DependencyInjection;

use Adeliom\EasyPageBundle\Controller\PageController;
use Adeliom\EasyPageBundle\Entity\Page;
use Adeliom\EasyPageBundle\Repository\PageRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_page');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('page_class')
                    ->isRequired()
                    ->validate()
                        ->ifString()
                        ->then(function($value) {
                            if (!class_exists($value) || !is_a($value, Page::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Page class must be a valid class extending %s. "%s" given.',
                                    Page::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->scalarNode('page_repository')
                    ->defaultValue(PageRepository::class)
                    ->validate()
                        ->ifString()
                        ->then(function($value) {
                            if (!class_exists($value) || !is_a($value, PageRepository::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Page repository must be a valid class extending %s. "%s" given.',
                                    PageRepository::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->scalarNode('page_controller')
                    ->defaultValue(PageController::class)
                    ->validate()
                        ->ifString()
                        ->then(function($value) {
                            if (!class_exists($value) || !is_a($value, PageController::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Page controller must be a valid class extending %s. "%s" given.',
                                    PageController::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->arrayNode('layouts')
                    ->defaultValue([
                        'front' => [
                            'resource' => '@EasyPage/default_layout.html.twig',
                            'pattern' => '',
                        ],
                    ])
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('resource')->isRequired()->end()
                            ->arrayNode('assets_css')->prototype('scalar')->end()->end()
                            ->arrayNode('assets_js')->prototype('scalar')->end()->end()
                            ->arrayNode('assets_webpack')->prototype('scalar')->end()->end()
                            ->scalarNode('pattern')->defaultValue('')->end()
                            ->scalarNode('host')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->integerNode('ttl')->defaultValue(300)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
