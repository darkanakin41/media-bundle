<?php

namespace PLejeune\MediaBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PLejeuneMediaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container,new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('plejeune.media.config',$processedConfig);
        $definition = $container->getDefinition('plejeune.media.fileupload');
        $definition->replaceArgument(0, $processedConfig);
    }

    public function prepend(ContainerBuilder $container)
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $container->prependExtensionConfig('twig', array('paths' => array(__DIR__.'/../Resources/views' => "PLejeuneMedia")));
    }

}