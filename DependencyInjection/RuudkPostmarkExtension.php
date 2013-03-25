<?php

/*
 * This file is part of the RuudkPostmarkBundle package.
 *
 * (c) Ruud Kamphuis <ruudk@mphuis.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ruudk\PostmarkBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class RuudkPostmarkExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if(isset($config['token'])) {
            $container->setParameter('ruudk_postmark.token', $config['token']);
        }

        if(isset($config['from_email'])) {
            $container->setParameter('ruudk_postmark.from_email', $config['from_email']);
        }

        if(isset($config['from_name'])) {
            $container->setParameter('ruudk_postmark.from_name', $config['from_name']);
        }

        if(isset($config['queue_name'])) {
            $container->setParameter('ruudk_postmark.queue_name', $config['queue_name']);
        }
    }
}