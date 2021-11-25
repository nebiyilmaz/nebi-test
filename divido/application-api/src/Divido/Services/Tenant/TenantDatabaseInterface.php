<?php

namespace Divido\Services\Tenant;

use Divido\ApiExceptions\TenantMissingOrInvalidException;

/**
 * Class TenantDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class TenantDatabaseInterface
{
    /**
     * @var \PDO
     */
    private $platformMasterDb;

    /**
     * @var \PDO
     */
    private $platformReadReplicaDb;

    /**
     * MerchantPortalDatabaseInterface constructor.
     * @param \PDO $platformMasterDb
     * @param \PDO $platformReadReplicaDb
     */
    function __construct(\PDO $platformMasterDb, \PDO $platformReadReplicaDb)
    {
        $this->platformMasterDb = $platformMasterDb;
        $this->platformReadReplicaDb = $platformReadReplicaDb;
    }

    /**
     * @param $data
     * @return Tenant
     * @throws \Exception
     */
    public function mapToModel($data): Tenant
    {
        $tenant = new Tenant();
        $tenant->setId($data->code)
            ->setName($data->name)
            ->setSettings(json_decode($data->settings, 1))
            ->setUpdatedAt(new \DateTime($data->updated_at))
            ->setCreatedAt(new \DateTime($data->created_at));

        return $tenant;
    }

    /**
     * @param $id
     * @param bool $useReadReplica
     * @return Tenant
     * @throws TenantMissingOrInvalidException
     */
    public function getTenantFromId($id, $useReadReplica = true): Tenant
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare(' 
            SELECT `name`, `code`, `settings`, `created_at`, `updated_at`
            FROM `platform_environment` 
            WHERE `code` = :id AND `deleted_at` IS NULL
            LIMIT 1');

        $statement->execute([
            ':id' => $id
        ]);

        if (!$statement->rowCount()) {
            throw new TenantMissingOrInvalidException($id);
        }

        $data = $statement->fetch();

        $model = $this->mapToModel($data);

        return $model;

    }

    /**
     * @param bool $useReadReplica
     * @return array
     * @throws \Exception
     */
    public function getAllTenants($useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            SELECT `id`, `settings`, `name`, `code`, `created_at`, `updated_at`
            FROM `platform_environment`
            WHERE `deleted_at` IS NULL');

        $statement->execute();

        $rows = $statement->fetchAll();

        $models = [];

        foreach ($rows as $data) {
            $model = $this->mapToModel($data);

            $models[] = $model;
        }

        return $models;

    }
}
