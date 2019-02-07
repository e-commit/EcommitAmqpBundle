<?php

namespace Ecommit\AmqpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ecommit_amqp');

        $rootNode
            ->children()
                ->arrayNode('rabbitmq')
                    ->isRequired()
                    ->children()
                        ->scalarNode('host')->isRequired()->end()
                        ->scalarNode('vhost')->isRequired()->end()
                        ->scalarNode('port')->isRequired()->end()
                        ->scalarNode('login')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('supervisor')
                    ->isRequired()
                    ->children()
                        ->scalarNode('host')->isRequired()->end()
                        ->integerNode('port')->isRequired()->end()
                        ->scalarNode('login')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                    ->end()
                ->end()
                ->scalarNode('sender')->isRequired()->end()
                ->arrayNode('admin_mail')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('attachment_mail')->defaultValue(null)->end()
                ->scalarNode('application_name')->isRequired()->end()
                ->scalarNode('error_template')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
