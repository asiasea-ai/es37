<?php

namespace Es37\Pool;

use EasySwoole\Pool\AbstractPool;
use EasySwoole\Pool\Config;
use Es37\Queue\Config\RabbitConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitPool extends AbstractPool
{
    protected RabbitConfig $rabbitConfig;

    /**
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public function __construct(Config $conf, RabbitConfig $rabbitConfig)
    {
        parent::__construct($conf);
        $this->rabbitConfig = $rabbitConfig;
    }

    /**
     * @throws \Exception
     */
    protected function createObject(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            $this->rabbitConfig->getHost(),
            $this->rabbitConfig->getPort(),
            $this->rabbitConfig->getUsername(),
            $this->rabbitConfig->getPassword(),
            $this->rabbitConfig->getVhost()
        );
    }
}