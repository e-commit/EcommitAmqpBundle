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
use Supervisor\Supervisor;
use Swift_Mailer;
use Swift_Transport_MailTransport;
use Symfony\Bundle\TwigBundle\TwigEngine;

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
     * @var TwigEngine
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
     * @return TwigEngine
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
