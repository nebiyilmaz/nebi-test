<?php

namespace Divido\Services\Refund;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Psr\Log\LoggerAwareTrait;

/**
 * Class RefundDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class RefundDatabaseInterface
{
    use LoggerAwareTrait;

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
     * @return Refund
     * @throws \Exception
     */
    public function mapToModel($data): Refund
    {
        $model = new Refund();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setStatus($data->status)
            ->setAmount($data->amount)
            ->setProductData(($data->product_data) ? array_values(json_decode($data->product_data, 1)) : [])
            ->setReference($data->reference)
            ->setComment($data->comment ?? '')
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param Refund $model
     * @return mixed
     */
    public function createNewRefundFromModel(Refund $model)
    {

        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_refund` 
          (
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `comment`
          )
          VALUES
	      (
	        :id, :application_id, :status, :amount, :product_data, :reference, :comment
	      )');

        $data = [
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':status' => $model->getStatus(),
            ':amount' => $model->getAmount(),
            ':product_data' => json_encode($model->getProductData()),
            ':reference' => $model->getReference(),
            ':comment' => $model->getComment(),
        ];

        $statement->execute($data);

        $this->logger->info(
            sprintf('Created refund record in DB for application: %s', $model->getApplicationId()),
            [
                'query_data' => $data,
            ]
        );

        return $model->getId();
    }

    /**
     * @param Refund $model
     * @return bool
     */
    public function updateRefundFromModel(Refund $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_refund` 
          SET 
            `status` = :status,
            `reference` = :reference, 
            `comment` = :comment
          WHERE id = :id 
          LIMIT 1');

        $statement->execute([
            ':id' => $model->getId(),
            ':status' => $model->getStatus(),
            ':reference' => $model->getReference(),
            ':comment' => $model->getComment()
        ]);

        return true;
    }

    /**
     * @param Refund $model
     * @param bool $useReadReplica
     * @return Refund
     * @throws ResourceNotFoundException
     */
    public function getRefundFromModel(Refund $model, $useReadReplica = true): Refund
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_refund`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_refund', 'id', $model->getId());
        }

        $data = $statement->fetch();

        $model = $this->mapToModel($data);

        return $model;

    }

    /**
     * @param Application $application
     * @param bool $useReadReplica
     * @return array
     * @throws \Exception
     */
    public function getAllRefunds(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_refund`
          WHERE `application_id` = :application_id');

        $statement->execute([
            ':application_id' => $application->getId()
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
