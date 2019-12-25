<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Darkanakin41MediaExtension extends Extension
{
    const CONFIG_KEY = 'darkanakin41.media.config';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::CONFIG_KEY, $processedConfig);

        if (!is_dir($processedConfig['base_folder'].$processedConfig['storage_folder'])) {
            mkdir($processedConfig['base_folder'].$processedConfig['storage_folder'], 0775, true);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $container->prependExtensionConfig('twig', array('paths' => array(__DIR__.'/../Resources/views' => 'Darkanakin41Media')));
    }
}
