<?php

namespace Divido\Services\Cancellation;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Psr\Log\LoggerAwareTrait;

/**
 * Class CancellationDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class CancellationDatabaseInterface
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
     * @return Cancellation
     * @throws \Exception
     */
    public function mapToModel($data): Cancellation
    {
        $model = new Cancellation();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setStatus($data->status)
            ->setAmount($data->amount)
            ->setReference($data->reference)
            ->setProductData(($data->product_data) ? array_values(json_decode($data->product_data, 1)) : [])
            ->setReference($data->reference)
            ->setComment($data->comment ?? '')
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param Cancellation $model
     * @return mixed
     */
    public function createNewCancellationFromModel(Cancellation $model)
    {
        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_cancellation` 
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
            sprintf('Created cancellation record in DB for application: %s', $model->getApplicationId()),
            [
                'query_data' => $data,
            ]
        );

        return $model->getId();
    }

    /**
     * @param Cancellation $model
     * @return bool
     */
    public function updateCancellationFromModel(Cancellation $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_cancellation` 
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
     * @param Cancellation $model
     * @param bool $useReadReplica
     * @return Cancellation
     * @throws ResourceNotFoundException
     */
    public function getCancellationFromModel(Cancellation $model, $useReadReplica = true): Cancellation
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_cancellation`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_cancellation', 'id', $model->getId());
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
    public function getAllCancellations(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_cancellation`
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
