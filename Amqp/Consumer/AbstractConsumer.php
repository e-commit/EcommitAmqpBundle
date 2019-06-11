<?php
/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\Amqp\Consumer;

use Ecommit\AmqpBundle\Manager\ServiceManager;
use Exception;
use Swift_Attachment;
use Swift_Message;
use Swift_Transport_SpoolTransport;

abstract class AbstractConsumer
{
    /**
     * @var ServiceManager
     */
    protected  $serviceManager = null;

    protected $stopped;
    protected $time;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Demarre le consmmer
     */
    public function run()
    {
        if ($this->serviceManager === null) {
            throw new Exception('Resource Manager must be defined');
        }

        $this->stopped = false;
        $this->time = time();

        $msg = null;
        try {
            $this->serviceManager->getBroker()->connect();

            $this->serviceManager->getLogger()->info($this->getName().' consumer started.');

            while (true) {
                pcntl_signal_dispatch();

                if ($this->stopped) {
                    $this->disconnect();

                    return;
                }

                $this->serviceManager->getBroker()->connect();

                while (false !== $msg = $this->serviceManager->getBroker()->consume($this->getName())) {

                    $this->consume($msg);

                    //Si transaction demarree dans la tache (et non commitee), commit ici
                    if ($this->serviceManager->getDoctrine()->getConnection()->isTransactionActive()) {
                        $this->serviceManager->getDoctrine()->getConnection()->commit();
                    }

                    //Envoi du spool de mail
                    $this->flushQueueMails();

                    //ACK
                    $this->serviceManager->getBroker()->ack($this->getName(), $msg);


                    $this->serviceManager->getDoctrine()->getManager()->clear(); // Detaches all objects from Doctrine!

                    $msg = null;

                    pcntl_signal_dispatch();

                    if ($this->stopped) {
                        $this->disconnect();

                        return;
                    }

                }

                if(time() >= $this->time + 3600) //Arret apres 1h
                {
                    $this->stop();
                }

                sleep(2);
            }
        } catch (\Exception $e) {
            $exceptionMessage = \sprintf('%s: %s (uncaught exception) at %s line %s while running consumer `%s`)',
                get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $this->getName());

            //Rollback si transaction en cours
            if($this->serviceManager->getDoctrine()->getConnection()->isTransactionActive())
            {
                $this->serviceManager->getDoctrine()->getConnection()->rollBack();
            }

            //Envoi mail à l'admin
            $body = $this->serviceManager->getTwig()->render($this->getErrorTemplate(), array_merge($this->getErrorTemplateParameters($e, $msg), array(
                'message_exception' => $exceptionMessage,
                'message_amqp' => $msg,
                'consumer_name' => $this->getName(),
            )));

            if (count($this->serviceManager->getAdminMail()) > 0) {
                $message = (new \Swift_Message())
                    ->setFrom($this->serviceManager->getSender())
                    ->setSubject(\sprintf('[%s] Task error', $this->serviceManager->getApplicationName()))
                    ->setBody($body, 'text/html')
                    ->setTo($this->serviceManager->getAdminMail());

                if ($this->getAttachmentMail()) {
                    $message->attach(Swift_Attachment::fromPath($this->getAttachmentMail()));
                }

                $this->serviceManager->getMailer()->send($message);
            }

            //Log
            $this->serviceManager->getLogger()->error($exceptionMessage);

            //Envoi du spool mail
            try {
                $this->flushQueueMails();
            } catch (\Exception $eMail) {
            }

            //Arret des taches
            try {
                $this->serviceManager->getLogger()->info(\sprintf('Stop %s group', $this->getSupervisorName()));
                $this->serviceManager->getSupervisorClient()->stopProcessGroup($this->getSupervisorName(), false); //false important pour ne pas attendre la fin: comme appel depuis lui meme
                sleep(10);
            }
            catch(Exception $e) {
            }

            return;
        }
    }

    /**
     * Demande d'arret du consumer
     */
    public function stop()
    {
        $this->serviceManager->getLogger()->info(\sprintf('Request: Stop %s consumer', $this->getName()));

        $this->stopped = true;
    }

    /**
     * Arret du consumer
     */
    public function disconnect()
    {
        $this->serviceManager->getBroker()->disconnect();
        $this->serviceManager->getLogger()->info(\sprintf('Stop %s consumer', $this->getName()));
    }

    public function flushQueueMails()
    {
        $transport = $this->serviceManager->getMailer()->getTransport();
        if ($transport instanceof Swift_Transport_SpoolTransport) {
            $spool = $transport->getSpool();
            if ($spool instanceof \Swift_MemorySpool) {
                $this->serviceManager->getLogger()->info('Flush Queue Mail');
                $spool->flushQueue($this->serviceManager->getMailerTransport());
            }
        }
    }

    /**
     * Retourne le nom du consumer (nom de la "queue" de rabbitmq)
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function getSupervisorName()
    {
        return $this->serviceManager->getApplicationName().'_'.$this->getName();
    }

    /**
     * @return mixed
     */
    protected function getErrorTemplate()
    {
        return $this->serviceManager->getErrorTemplate();
    }

    /**
     * @return mixed
     */
    protected function getAttachmentMail()
    {
        return $this->serviceManager->getAttachmentMail();
    }

    /**
     * @return mixed
     */
    protected function getErrorTemplateParameters(\Exception $exception, $amqpMessage)
    {
        return array();
    }

    abstract public function consume(\AMQPEnvelope $msg);
}
