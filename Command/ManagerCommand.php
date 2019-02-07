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

use Ecommit\AmqpBundle\Amqp\Broker;
use Ecommit\AmqpBundle\Amqp\Supervisor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerCommand extends Command
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var Supervisor
     */
    protected $supervisor;

    protected $amqpApplicationName;

    public function __construct(Broker $broker, Supervisor $supervisor, $amqpApplicationName)
    {
        $this->broker = $broker;
        $this->supervisor = $supervisor;
        $this->amqpApplicationName = $amqpApplicationName;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('amqp:manager')
            ->setDescription('Enable / Disable tasks')
            ->addArgument('action', InputArgument::REQUIRED, 'start|stop|status')
            ->addArgument('consumer', InputArgument::REQUIRED, 'Consumer name or "all"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $input->getArgument('consumer');
        if ($consumer == 'all') {
            $consumers = $this->broker->getConsumersNames();
        } elseif ($this->broker->getConsumer($consumer)) {
            $consumers = array($consumer);
        }

        $supervisorGroups = array();
        foreach ($consumers as $consumerName) {
            $supervisorGroups[] =  sprintf('%s_%s', $this->amqpApplicationName, $consumerName);
        }

        switch ($input->getArgument('action')) {
            case 'start':
                $this->startAction($supervisorGroups, $output);
                break;
            case 'stop':
                $this->stopAction($supervisorGroups, $output);
                break;
            case 'status':
                $this->displayStatusAction($supervisorGroups, $output);
                break;
            default:
                $output->writeln('<error>Bad action</error>');

                return;
        }
    }

    private function startAction($supervisorGroups, OutputInterface $output)
    {
        foreach ($supervisorGroups as $supervisorGroup) {
            $output->writeln(\sprintf('Starting %s group', $supervisorGroup));
            $this->supervisor->startProcessGroup($supervisorGroup, true);
            $output->writeln(\sprintf('%s group is started', $supervisorGroup));
        }
    }

    private function stopAction($supervisorGroups, OutputInterface $output)
    {
        foreach ($supervisorGroups as $supervisorGroup) {
            $output->writeln(\sprintf('Stopping %s group', $supervisorGroup));
            $this->supervisor->stopProcessGroup($supervisorGroup, true);
            $output->writeln(\sprintf('%s group is stopped', $supervisorGroup));
        }
    }

    private function displayStatusAction($supervisorGroups, OutputInterface $output)
    {
        foreach ($this->supervisor->getAllProcessInfo() as $process) {
            if (in_array($process['group'], $supervisorGroups)) {
                $output->writeln(
                    \sprintf(
                        '%s - %s : %s (PID %s)',
                        $process['group'],
                        $process['name'],
                        $process['statename'],
                        $process['pid']
                    )
                );
            }
        }
    }
}
