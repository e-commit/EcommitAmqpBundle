<?php
/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\Amqp;

use Ecommit\AmqpBundle\Amqp\Consumer\AbstractConsumer;
use Ecommit\AmqpBundle\Exception\ConsumerNotFoundException;
use Exception;

class Broker
{
    protected $consumers;

    protected $conn;
    protected $exchanges;
    protected $queues;
    protected $channel;

    public function __construct($user, $password, $host, $port, $vhost)
    {
        $this->conn = new \AMQPConnection(array(
                'host' => $host,
                'vhost' => $vhost,
                'port' => $port,
                'login' => $user,
                'password' => $password,
            ));
        $this->exchanges = array();
        $this->queues = array();
        $this->consumers = array();
    }

    public function addConsumer(AbstractConsumer $consumer)
    {
        $this->consumers[$consumer->getName()] = $consumer;
    }

    public function getConsumer($name)
    {
        if (array_key_exists($name, $this->consumers)) {
            return $this->consumers[$name];
        }

        $alternatives = $this->getConsumersNames();
        $message = 'Consumer not found';
        $message .= "\n\nDid you mean one of these?\n    ";
        $message .= implode("\n    ", $alternatives);

        throw new ConsumerNotFoundException($message, $alternatives);
    }

    public function getConsumersNames()
    {
        return array_keys($this->consumers);
    }

    public  function consume($queueName)
    {
        $this->connect();
        return $this->queues[$queueName]->get();
    }

    public function ack($queueName, \AMQPEnvelope $msg)
    {
        $this->connect();
        $this->queues[$queueName]->ack($msg->getDeliveryTag());
    }

    public function isConnected()
    {
        return $this->conn->isConnected();
    }

    public function disconnect()
    {
        if ($this->conn->isConnected()) {
            $this->conn->disconnect();
        }
    }

    public function connect()
    {
        if ($this->conn->isConnected()) {
            return;
        }

        $this->conn->reconnect();

        $this->channel = new \AMQPChannel($this->conn);

        $exchanges = $this->getConsumersNames();

        // Exchanges
        foreach ($exchanges as $name) {
            $this->exchanges[$name] = $this->createExchange($name);
        }

        // Queues
        foreach ($exchanges as $name) {
            $this->queues[$name] = $this->createQueue($name);
            $this->queues[$name]->bind($name, $name);
        }
    }

    protected function createExchange($name)
    {
        if (!$this->conn->isConnected()) {
            throw new Exception('Can not create exchange if not connected.');
        }

        $exchange = new \AMQPExchange($this->channel);
        $exchange->setName($name);
        $exchange->setType(\AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(\AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange;
    }

    protected function createQueue($name, array $arguments = array())
    {
        if (!$this->conn->isConnected()) {
            throw new Exception('Can not create queue if not connected.');
        }

        $queue = new \AMQPQueue($this->channel);
        $queue->setName($name);
        $queue->setFlags(\AMQP_DURABLE);
        if ($arguments) {
            $queue->setArguments($arguments);
        }
        $queue->declareQueue();

        return $queue;
    }

    public function submit($queueName, $message)
    {
        $this->connect();
        $this->exchanges[$queueName]->publish($message, $queueName, \AMQP_MANDATORY, array('delivery_mode' => 2));
    }
}
