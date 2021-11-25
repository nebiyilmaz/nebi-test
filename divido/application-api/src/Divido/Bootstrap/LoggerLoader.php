<?php

namespace Divido\Bootstrap;

use Divido\LogStreamer\Logger;
use Psr\Container\ContainerInterface;

/**
 * Class LoggerLoader
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class LoggerLoader
{
    /**
     * @param ContainerInterface $container
     * @throws \Divido\LogStreamer\InvalidLogChannelException
     */
    public function load(ContainerInterface $container)
    {
        $log = new Logger('application-api');
        $log->addFileLog('php://stdout');
        $log->addDataDog();

        $container['Logger'] = $log;
    }
}
