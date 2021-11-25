<?php

namespace Divido\Services\AlternativeOffer;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;

/**
 * Class AlternativeOfferDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class AlternativeOfferDatabaseInterface
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
     * @return AlternativeOffer
     */
    public function mapToModel($data): AlternativeOffer
    {
        $model = new AlternativeOffer();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setLenderId($data->lender_id)
            ->setData(json_decode($data->data))
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param AlternativeOffer $model
     * @return mixed
     */
    public function createNewAlternativeOfferFromModel(AlternativeOffer $model)
    {
        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_alternative_offer` 
          (
            `id`, `application_id`, `lender_id`, `data`
          )
          VALUES
	      (
	        :id, :application_id, :lender_id, :data
	      )');

        $statement->execute([
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':lender_id' => $model->getLenderId(),
            ':data' => json_encode($model->getData())
        ]);

        return $model->getId();
    }

    /**
     * @param AlternativeOffer $model
     * @return bool
     */
    public function updateAlternativeOfferFromModel(AlternativeOffer $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_alternative_offer` 
          SET 
            `data` = :data
          WHERE id = :id 
          LIMIT 1');

        $statement->execute([
            ':id' => $model->getId(),
            ':data' => json_encode($model->getData())
        ]);

        return true;
    }

    /**
     * @param AlternativeOffer $model
     * @param bool $useReadReplica
     * @return AlternativeOffer
     * @throws ResourceNotFoundException
     */
    public function getAlternativeOfferFromModel(AlternativeOffer $model, $useReadReplica = true): AlternativeOffer
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `lender_id`, `data`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_alternative_offer`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_alternative_offer', 'id', $model->getId());
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
    public function getAllAlternativeOffers(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `lender_id`, `data`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_alternative_offer`
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
