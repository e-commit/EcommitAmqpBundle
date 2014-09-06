<?php
/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('amqp:run')
            ->setDescription('Run a rabbitmq consumer.')
            ->setDefinition(
                array(
                    new InputArgument('daemon', InputArgument::REQUIRED, 'The daemon'),
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $daemon = $this->getContainer()->get('ecommit_amqp.broker')->getConsumer($input->getArgument('daemon'));

        pcntl_signal(
            SIGTERM,
            function () use ($daemon) {
                $daemon->stop();
            }
        );
        pcntl_signal(
            SIGINT,
            function () use ($daemon) {
                $daemon->stop();
            }
        );

        $daemon->run();
    }
}
