<?php

namespace Ecommit\AmqpBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EcommitAmqpExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('ecommit_amqp.rabbitmq.host', $config['rabbitmq']['host']);
        $container->setParameter('ecommit_amqp.rabbitmq.vhost', $config['rabbitmq']['vhost']);
        $container->setParameter('ecommit_amqp.rabbitmq.port', $config['rabbitmq']['port']);
        $container->setParameter('ecommit_amqp.rabbitmq.login', $config['rabbitmq']['login']);
        $container->setParameter('ecommit_amqp.rabbitmq.password', $config['rabbitmq']['password']);

        //Define URL
        $supervisorUrl = $config['supervisor']['host'].':'.$config['supervisor']['port'].'/RPC2';
        if (!preg_match('/^http:\/\//', $supervisorUrl)) {
            $supervisorUrl = 'http://'.$supervisorUrl;
        }

        $container->setParameter('ecommit_amqp.supervisor.url', $supervisorUrl);
        $container->setParameter('ecommit_amqp.supervisor.login', $config['supervisor']['login']);
        $container->setParameter('ecommit_amqp.supervisor.password', $config['supervisor']['password']);

        $container->setParameter('ecommit_amqp.sender', $config['sender']);
        $container->setParameter('ecommit_amqp.admin_mail', $config['admin_mail']);
        $container->setParameter('ecommit_amqp.attachment_mail', $config['attachment_mail']);
        $container->setParameter('ecommit_amqp.application_name', $config['application_name']);
        $container->setParameter('ecommit_amqp.error_template', $config['error_template']);
    }
}

