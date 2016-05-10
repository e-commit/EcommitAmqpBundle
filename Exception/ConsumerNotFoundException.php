<?php

/**
 * This file is part of the EcommitAmqpBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\AmqpBundle\Exception;

class ConsumerNotFoundException extends \InvalidArgumentException
{
    private $alternatives;

    public function __construct($message, array $alternatives = array(), $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->alternatives = $alternatives;
    }

    /**
     * @return array
     */
    public function getAlternatives()
    {
        return $this->alternatives;
    }
}
