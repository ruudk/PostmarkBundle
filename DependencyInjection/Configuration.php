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
                ->arrayNode('from')
                    ->children()
                        ->scalarNode('email')
                            ->defaultNull()
                            ->end()
                        ->scalarNode('name')
                            ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode('resque')
                    ->children()
                        ->scalarNode('queue')
                            ->defaultValue('postmark')
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode('curl')
                    ->children()
                        ->scalarNode('timeout')
                            ->end()
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
