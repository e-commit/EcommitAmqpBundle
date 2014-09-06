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

use Swift_Message;
use Swift_Transport_SpoolTransport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('amqp:check')
            ->setDescription('Check tasks')
            ->addOption('send-mail', null, InputOption::VALUE_NONE, 'Send mail (or not)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
                    $output->writeln(
                        \sprintf(
                            '<fg=green>%s - %s : %s (PID %s)</fg=green>',
                            $process['group'],
                            $process['name'],
                            $process['statename'],
                            $process['pid']
                        )
                    );
                } else {
                    $countStopped++;
                    $output->writeln(
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

        if (($countStarted == 0 || $countStopped > 1) && $input->getOption('send-mail')) {
            //Envoi mail
            $message = Swift_Message::newInstance()
                ->setFrom($this->getContainer()->getParameter('ecommit_amqp.sender'))
                ->setSubject(sprintf('[%s] WARNING - Tasks disabled', $this->getContainer()->getParameter('ecommit_amqp.application_name')))
                ->setBody(sprintf('[%s] WARNING - Tasks disabled', $this->getContainer()->getParameter('ecommit_amqp.application_name')))
                ->setTo($this->getContainer()->getParameter('ecommit_amqp.admin_mail'));
            $this->getContainer()->get('mailer')->send($message);
            $this->flushQueueMails();
        }
    }

    protected function flushQueueMails()
    {
        $transport = $this->getContainer()->get('mailer')->getTransport();
        if ($transport instanceof Swift_Transport_SpoolTransport) {
            $spool = $transport->getSpool();
            $spool->flushQueue($this->getContainer()->get('swiftmailer.transport.real'));
        }
    }
}
