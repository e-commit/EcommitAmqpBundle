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

use Ecommit\AmqpBundle\Amqp\Supervisor;
use fXmlRpc\Client;
use fXmlRpc\Transport\HttpAdapterTransport;
use Supervisor\Connector\XmlRpc;

class SupervisorFactory
{
    /**
     * @param string $url
     * @param string $login
     * @param string $password
     * @return Supervisor
     */
    public static function createSupervisor($url, $login, $password)
    {
        $guzzleClient = new \GuzzleHttp\Client([
            'auth' => [$login, $password],
            'timeout' => 3600
        ]);

        //Pass the url and the bridge to the XmlRpc Client
        $client = new Client(
            $url,
            new HttpAdapterTransport(
                new \Http\Message\MessageFactory\GuzzleMessageFactory(),
                new \Http\Adapter\Guzzle6\Client($guzzleClient)
            )
        );

        //Pass the client to the connector
        $connector = new XmlRpc($client);

        return new Supervisor($connector);
    }
}
