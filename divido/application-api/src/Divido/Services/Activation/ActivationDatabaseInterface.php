<?php

namespace Divido\Services\Activation;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ActivationDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ActivationDatabaseInterface
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
     * @return Activation
     */
    public function mapToModel($data): Activation
    {
        $model = new Activation();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setStatus($data->status)
            ->setReference($data->reference)
            ->setAmount($data->amount)
            ->setProductData(($data->product_data) ? array_values(json_decode($data->product_data, 1)) : [])
            ->setDeliveryMethod($data->delivery_method)
            ->setTrackingNumber($data->tracking_number)
            ->setComment($data->comment ?? '')
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param Activation $model
     * @return mixed
     */
    public function createNewActivationFromModel(Activation $model)
    {
        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_activation` 
          (
            `id`, `application_id`, `status`, `amount`, `product_data`, `delivery_method`, `tracking_number`, `reference`, `comment`
          )
          VALUES
	      (
	        :id, :application_id, :status, :amount, :product_data, :delivery_method, :tracking_number, :reference, :comment
	      )');

        $data = [
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':status' => $model->getStatus(),
            ':amount' => $model->getAmount(),
            ':product_data' => json_encode($model->getProductData()),
            ':delivery_method' => $model->getDeliveryMethod(),
            ':tracking_number' => $model->getTrackingNumber(),
            ':reference' => $model->getReference(),
            ':comment' => $model->getComment(),
        ];

        $statement->execute($data);

        $this->logger->info(
            sprintf('Created activation record in DB for application: %s', $model->getApplicationId()),
            [
                'query_data' => $data,
            ]
        );

        return $model->getId();
    }

    /**
     * @param Activation $model
     * @return bool
     */
    public function updateActivationFromModel(Activation $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_activation` 
          SET 
            `status` = :status,
            `reference` = :reference, 
            `delivery_method` = :delivery_method,
            `tracking_number` = :tracking_number,
            `comment` = :comment
          WHERE id = :id 
          LIMIT 1');

        $statement->execute([
            ':id' => $model->getId(),
            ':status' => $model->getStatus(),
            ':reference' => $model->getReference(),
            ':delivery_method' => $model->getDeliveryMethod(),
            ':tracking_number' => $model->getTrackingNumber(),
            ':comment' => $model->getComment()
        ]);

        return true;
    }

    /**
     * @param Activation $model
     * @param bool $useReadReplica
     * @return Activation
     * @throws ResourceNotFoundException
     */
    public function getActivationFromModel(Activation $model, $useReadReplica = true): Activation
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `delivery_method`, `tracking_number`,`comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_activation`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_activation', 'id', $model->getId());
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
    public function getAllActivations(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `status`, `amount`, `product_data`, `reference`, `delivery_method`, `tracking_number`,`comment`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_activation`
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
