<?php
/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\DependencyInjection\Compiler;

use Ecommit\AmqpBundle\Amqp\Broker;
use Ecommit\AmqpBundle\Manager\ServiceManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConsumerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $broker = $container->getDefinition(
            Broker::class
        );

        $serviceManager = $container->getDefinition(
            ServiceManager::class
        );

        $taggedServices = $container->findTaggedServiceIds(
            'ecommit_amqp.consumer'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            $broker->addMethodCall(
                'addConsumer',
                array(new Reference($id))
            );
            $container->getDefinition($id)->addMethodCall(
                'setServiceManager',
                array($serviceManager)
            );
        }
    }
}
