<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class LenonLeiteFilterValidEmailRecipientExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Config'));
        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.project_dir%/plugins/LenonLeiteFilterValidEmailRecipientBundle/Resources/LeadBundle/views' => 'MauticLead', // You use the namespace you found earlier here. Discard the `@` symbol.
            ),
        ));
    }
}