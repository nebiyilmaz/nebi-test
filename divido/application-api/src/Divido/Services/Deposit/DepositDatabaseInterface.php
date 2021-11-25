<?php

namespace Divido\Services\Deposit;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;

/**
 * Class DepositDatabaseInterface
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class DepositDatabaseInterface
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
     */
    function __construct(\PDO $platformMasterDb, \PDO $platformReadReplicaDb)
    {
        $this->platformMasterDb = $platformMasterDb;
        $this->platformReadReplicaDb = $platformReadReplicaDb;
    }

    /**
     * @param $data
     * @return Deposit
     */
    public function mapToModel($data): Deposit
    {
        $model = new Deposit();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setStatus($data->status)
            ->setAmount($data->amount)
            ->setMerchantComment($data->merchant_comment)
            ->setType($data->type)
            ->setReference($data->reference)
            ->setDataRaw(json_decode($data->data_raw))
            ->setProductData(json_decode($data->product_data))
            ->setMerchantReference($data->merchant_reference)
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param Deposit $model
     * @return mixed
     */
    public function createNewDepositFromModel(Deposit $model)
    {
        $statement = $this->platformMasterDb->prepare('
        INSERT INTO `application_deposit`
            (
                `id`,
                `application_id`,
                `status`,
                `amount`,
                `product_data`,
                `merchant_comment`,
                `type`,
                `reference`,
                `data_raw`,
                `merchant_reference`
            ) VALUES (
                :id,
                :application_id,
                :status,
                :amount,
                :product_data,
                :merchant_comment,
                :type,
                :reference,
                :data_raw,
                :merchant_reference
            )
        ');

        $statement->execute([
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':status' => $model->getStatus(),
            ':amount' => $model->getAmount(),
            ':product_data' => json_encode($model->getProductData()),
            ':merchant_comment' => $model->getMerchantComment(),
            ':type' => $model->getType(),
            ':reference' => $model->getReference(),
            ':data_raw' => json_encode($model->getDataRaw()),
            ':merchant_reference' => $model->getMerchantReference(),
        ]);

        return $model->getId();
    }

    /**
     * @param Deposit $model
     * @return bool
     */
    public function updateDepositFromModel(Deposit $model)
    {
        $statement = $this->platformMasterDb->prepare('
            UPDATE `application_deposit`
            SET
                `status` => :status,
                `amount` => :amount,
                `product_data` => :product_data,
                `merchant_comment` => :merchant_comment,
                `type` => :type,
                `reference` => :reference,
                `data_raw` => :data_raw,
                `merchant_reference` => :merchant_reference
            WHERE id = :id
            LIMIT 1
        ');

        $statement->execute([
            ':status' => $model->getStatus(),
            ':amount' => $model->getAmount(),
            ':product_data' => $model->getProductData(),
            ':merchant_comment' => $model->getMerchantComment(),
            ':type' => $model->getType(),
            ':reference' => $model->getReference(),
            ':data_raw' => $model->getDataRaw(),
            ':merchant_reference' => $model->getMerchantReference(),
        ]);

        return true;
    }

    /**
     * @param Deposit $model
     * @param bool $useReadReplica
     * @return Deposit
     * @throws ResourceNotFoundException
     */
    public function getDepositFromModel(Deposit $model, $useReadReplica = true): Deposit
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('
            SELECT
                `id`,
                `application_id`,
                `status`,
                `amount`,
                `merchant_comment`,
                `type`,
                `reference`,
                `data_raw`,
                `product_data`,
                `merchant_reference`,
                `created_at`,
                COALESCE(`updated_at`, `created_at`) AS `updated_at`
            FROM `application_deposit`
            WHERE `id` = :id
        ');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_deposit', 'id', $model->getId());
        }

        $data = $statement->fetch();

        $model = $this->mapToModel($data);

        return $model;
    }

    /**
     * @param Application $application
     * @param bool $useReadReplica
     * @return array
     */
    public function getAllDeposits(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('
            SELECT
                `id`,
                `application_id`,
                `status`,
                `amount`,
                `merchant_comment`,
                `type`,
                `reference`,
                `data_raw`,
                `product_data`,
                `merchant_reference`,
                `created_at`,
                COALESCE(`updated_at`, `created_at`) AS `updated_at`
            FROM `application_deposit`
            WHERE `application_id` = :application_id
        ');

        $statement->execute([
            ':application_id' => $application->getId(),
        ]);

        $rows = $statement->fetchAll();

        $models = [];

        foreach ($rows as $data) {
            $model = $this->mapToModel($data);
            $models[] = $model;
        }

        return $models;
    }
}
