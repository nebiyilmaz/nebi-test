<?php

namespace Divido\Services\Health;

use Divido\Traits\RedisAwareTrait;

/**
 * Class HealthService
 *
 * @copyright (c) 2019, Divido
 */
class HealthService
{
    use RedisAwareTrait;

    /**
     * @var HealthDatabaseInterface
     */
    private $healthDatabaseInterface;

    /**
     * HealthService constructor.
     * @param HealthDatabaseInterface $healthDatabase
     */
    function __construct(HealthDatabaseInterface $healthDatabase)
    {
        $this->healthDatabaseInterface = $healthDatabase;
    }

    /**
     * @return Health
     */
    public function check()
    {
        return $this->healthDatabaseInterface->checkHealth();
    }
}
