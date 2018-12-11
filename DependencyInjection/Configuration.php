<?php

namespace PLejeune\MediaBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('p_lejeune_media');

        $rootNode->children()
            ->scalarNode('storage_folder')->end()
            ->arrayNode('image_formats')
                ->arrayPrototype()
                    ->arrayPrototype()
                        ->children()
                            ->integerNode('width')->end()
                            ->integerNode('height')->end()
                            ->integerNode('min_width')->end()
                            ->integerNode('quality')->end()
                            ->scalarNode('resize')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('categories')
                ->scalarPrototype()
                ->end()
            ->end();

        return $treeBuilder;
    }
}