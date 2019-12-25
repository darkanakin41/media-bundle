<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('darkanakin41_media');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('darkanakin41_media');
        }

        $rootNode->children()
            ->scalarNode('base_folder')->defaultValue('%kernel.project_dir%/public/')->end()
            ->scalarNode('storage_folder')->defaultValue('media/')->end()
            ->booleanNode('resize')->defaultValue(false)->end()
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
