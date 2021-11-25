<?php

namespace Divido\Bootstrap;

use Divido\Cache\RedisAdapter;
use Psr\Container\ContainerInterface;

/**
 * Class UtilitiesLoader
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class UtilitiesLoader
{
    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param ContainerInterface $container
     */
    public function loadDatabases(ContainerInterface $container)
    {
        $container['Database.Platform.Master'] = function () use ($container) {
            $dsn = vsprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', [
                $container['Config']->get('database.mysql.platform.master.host'),
                $container['Config']->get('database.mysql.platform.master.port'),
                $container['Config']->get('database.mysql.platform.master.database'),
            ]);

            $pdo = new \PDO(
                $dsn,
                $container['Config']->get('database.mysql.platform.master.username'),
                $container['Config']->get('database.mysql.platform.master.password'),
                [
                    \PDO::ATTR_PERSISTENT => true
                ]
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

            return $pdo;
        };

        $container['Database.Platform.ReadReplica'] = function () use ($container) {

            $dsn = vsprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', [
                $container['Config']->get('database.mysql.platform.read_replica.host'),
                $container['Config']->get('database.mysql.platform.read_replica.port'),
                $container['Config']->get('database.mysql.platform.read_replica.database'),
            ]);

            $pdo = new \PDO(
                $dsn,
                $container['Config']->get('database.mysql.platform.read_replica.username'),
                $container['Config']->get('database.mysql.platform.read_replica.password'),
                [
                    \PDO::ATTR_PERSISTENT => true
                ]
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

            return $pdo;
        };

    }

    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param ContainerInterface $container
     */
    public function loadRedis(ContainerInterface $container)
    {
        $container['Redis'] = function () {
            return new \Redis;
        };

        $container['Cache'] = function () use ($container) {
            if (!$container->get('Config')->get('database.redis.disabled') && $container->get('Config')->get('database.redis.host')) {
                $host = $container->get('Config')->get('database.redis.host');
            }

            return new RedisAdapter(
                $container->get('Redis'),
                $host ?? false
            );
        };

    }
}
