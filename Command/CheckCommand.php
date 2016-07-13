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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('amqp:check')
            ->setDescription('Check tasks')
            ->addOption('nagios', null, InputOption::VALUE_NONE, 'Suitable for using as a nagios NRPE command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $countStopped = 0;
        $countStarted = 0;

        $supervisorGroups = array();
        foreach ($this->getContainer()->get('ecommit_amqp.broker')->getConsumersNames() as $consumerName) {
            $supervisorGroups[] =  sprintf('%s_%s', $this->getContainer()->getParameter('ecommit_amqp.application_name'), $consumerName);
        }

        $supervisor = $this->getContainer()->get('ecommit_amqp.supervisor');
        foreach ($supervisor->getAllProcessInfo() as $process) {
            if (in_array($process['group'], $supervisorGroups)) {
                if ($process['statename'] == 'RUNNING') {
                    //Processus en cours de fonctionnement
                    $countStarted++;
                    if (!$input->getOption('nagios')) {
                        $output->writeln(
                            \sprintf(
                                '<fg=green>%s - %s : %s (PID %s)</fg=green>',
                                $process['group'],
                                $process['name'],
                                $process['statename'],
                                $process['pid']
                            )
                        );
                    }
                } else {
                    $countStopped++;
                    if (!$input->getOption('nagios')) {
                        $errOutput->writeln(
                            \sprintf(
                                '<fg=red>%s - %s : %s</fg=red>',
                                $process['group'],
                                $process['name'],
                                $process['statename']
                            )
                        );
                    }
                }
            }
        }

        if ($countStarted == 0 || $countStopped > 0) {
            if ($input->getOption('nagios')) {
                $output->writeln(\sprintf('CRITICAL - Running tasks: %s Stopped tasks: %s', $countStarted, $countStopped));
            }

            return 2;
        }

        if ($input->getOption('nagios')) {
            $output->writeln(\sprintf('OK - Running tasks: %s Stopped tasks: %s', $countStarted, $countStopped));
        }

        return 0;
    }
}
