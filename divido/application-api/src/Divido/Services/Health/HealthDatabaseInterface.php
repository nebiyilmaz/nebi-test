<?php

namespace Divido\Services\Health;

/**
 * Class HealthDatabaseInterface
 *
 * @copyright (c) 2019, Divido
 */
class HealthDatabaseInterface
{
    /**
     * @var \PDO
     */
    private $readReplicaDb;

    /**
     * ApiKeyDatabaseInterface constructor.
     * @param \PDO $signerMasterDb
     */
    function __construct(\PDO $readReplicaDb)
    {
        $this->readReplicaDb = $readReplicaDb;
    }

    /**
     * @return Health
     */
    public function checkHealth()
    {
        $statement = $this->readReplicaDb->prepare('select now() as checked_at');
        $statement->execute();

        $data = $statement->fetch();

        $checkedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $data->checked_at);

        $model = new Health();
        $model->setCheckedAt($checkedAt);

        return $model;
    }
}
