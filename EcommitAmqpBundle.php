<?php

namespace Ecommit\AmqpBundle;

use Ecommit\AmqpBundle\DependencyInjection\Compiler\ConsumerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EcommitAmqpBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConsumerCompilerPass());
    }
}
