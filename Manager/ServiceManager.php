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

use Doctrine\Persistence\ManagerRegistry;
use Ecommit\AmqpBundle\Amqp\Broker;
use Ecommit\AmqpBundle\Amqp\Supervisor;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Transport;
use Twig\Environment;

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
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Swift_Transport
     */
    protected $mailerTransport;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var Supervisor
     */
    protected $supervisorClient;

    protected $sender;

    protected $adminMail;

    protected $applicationName;

    protected $errorTemplate;

    protected $attachmentMail;

    public function __construct($adminMail, $broker, $doctrine, $logger, $mailer, $mailerTransport, $sender, $twig, $supervisorClient, $applicationName, $errorTemplate, $attachmentMail)
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
        $this->attachmentMail = $attachmentMail;
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
     * @return ManagerRegistry
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
     * @return Swift_Transport
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
     * @return Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @return Supervisor
     */
    public function getSupervisorClient()
    {
        return $this->supervisorClient;
    }

    /**
     * @param Supervisor $supervisorClient
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

    /**
     * @return mixed
     */
    public function getAttachmentMail()
    {
        return $this->attachmentMail;
    }
}
