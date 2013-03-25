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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ruudk_postmark');

        $rootNode
            ->children()
                ->scalarNode('token')
                    ->isRequired()
                    ->end()
                ->scalarNode('from_email')
                    ->defaultNull()
                    ->end()
                ->scalarNode('from_name')
                    ->defaultNull()
                    ->end()
                ->scalarNode('queue_name')
                    ->defaultValue('postmark')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
