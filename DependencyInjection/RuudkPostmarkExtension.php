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

        if(isset($config['delayed']) && $config['delayed'] === true) {
            $container->getDefinition('ruudk_postmark.postmark')->addMethodCall("delayed", array(true));
        }

        if(isset($config['from'])) {
            $container->getDefinition('ruudk_postmark.postmark')->addMethodCall("setFrom", array(
                $config['from']['email'],
                isset($config['from']['name']) ? $config['from']['name'] : null
            ));
        }

        if(isset($config['resque'])) {
            if(isset($config['resque']['queue'])) {
                $container->setParameter('ruudk_postmark.resque.queue', $config['resque']['queue']);
            }
        }

        if(isset($config['curl'])) {
            if(isset($config['curl']['timeout'])) {
                $container->getDefinition('ruudk_postmark.curl')->addMethodCall("setTimeout", array($config['curl']['timeout']));
            }
        }
    }
}