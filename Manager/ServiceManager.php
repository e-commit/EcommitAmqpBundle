<?php
/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Ecommit\AmqpBundle\Amqp\Broker;
use Psr\Log\LoggerInterface;
use SupervisorClient\SupervisorClient;
use Swift_Mailer;
use Swift_Transport_MailTransport;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;

class ServiceManager
{
    /**
     * @var Broker
     */
    protected  $broker;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Swift_Transport_MailTransport
     */
    protected $mailerTransport;

    /**
     * @var TimedTwigEngine
     */
    protected $twig;

    /**
     * @var SupervisorClient
     */
    protected $supervisorClient;

    protected $sender;

    protected $adminMail;

    protected $applicationName;

    protected $errorTemplate;

    function __construct($adminMail, $broker, $doctrine, $logger, $mailer, $mailerTransport, $sender, $twig, $supervisorClient, $applicationName, $errorTemplate)
    {
        $this->adminMail = $adminMail;
        $this->broker = $broker;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->mailerTransport = $mailerTransport;
        $this->sender = $sender;
        $this->twig = $twig;
        $this->supervisorClient = $supervisorClient;
        $this->applicationName = $applicationName;
        $this->errorTemplate = $errorTemplate;
    }

    /**
     * @return mixed
     */
    public function getAdminMail()
    {
        return $this->adminMail;
    }

    /**
     * @return Broker
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return Swift_Transport_MailTransport
     */
    public function getMailerTransport()
    {
        return $this->mailerTransport;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return TimedTwigEngine
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @return SupervisorClient
     */
    public function getSupervisorClient()
    {
        return $this->supervisorClient;
    }

    /**
     * @param SupervisorClient $supervisorClient
     */
    public function setSupervisorClient($supervisorClient)
    {
        $this->supervisorClient = $supervisorClient;
    }

    /**
     * @return mixed
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * @return mixed
     */
    public function getErrorTemplate()
    {
        return $this->errorTemplate;
    }
}
