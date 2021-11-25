<?php

namespace Divido\Services\Signatory;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;

/**
 * Class SignatoryDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class SignatoryDatabaseInterface
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
     * @return Signatory
     * @throws \Exception
     */
    public function mapToModel($data): Signatory
    {
        $model = new Signatory();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setFirstName($data->first_name)
            ->setLastName($data->last_name)
            ->setEmailAddress($data->email)
            ->setTitle($data->title)
            ->setDateOfBirth(new \DateTime($data->text))
            ->setLenderReference($data->lender_reference)
            ->setHostedSigning(($data->hosted_signing) ? 1:0)
            ->setDataRaw(json_decode($data->data_raw ?? "{}"))
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param Signatory $model
     * @return mixed
     */
    public function createNewSignatoryFromModel(Signatory $model)
    {
        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_signatory` 
          (
            `id`, `application_id`, `first_name`, `last_name`, `email`, `title`, `date_of_birth`, `lender_reference`, `hosted_signing`, `data_raw`
          )
          VALUES
	      (
	        :id, :application_id, :first_name, :last_name, :email_address, :title, :date_of_birth, :lender_reference, :hosted_signing, :data_raw
	      )');

        $statement->execute([
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':first_name' => $model->getFirstName(),
            ':last_name' => $model->getLastName(),
            ':email_address' => $model->getEmailAddress(),
            ':title' => $model->getTitle(),
            ':date_of_birth' => $model->getDateOfBirth()->format("Y-m-d"),
            ':lender_reference' => $model->getLenderReference(),
            ':hosted_signing' => ($model->isHostedSigning()) ? 1:0,
            ':data_raw' => json_encode($model->getDataRaw()),
        ]);

        return $model->getId();
    }

    /**
     * @param Signatory $model
     * @return bool
     */
    public function updateSignatoryFromModel(Signatory $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_signatory` 
          SET 
            `first_name` = :first_name,
            `last_name` = :last_name, 
            `email` = :email_address,
            `title` = :title,
            `date_of_birth` = :date_of_birth,
            `lender_reference` = :lender_reference,
            `hosted_signing` = :hosted_signing,
            `data_raw` = :data_raw
          WHERE id = :id 
          LIMIT 1');

        $statement->execute([
            ':id' => $model->getId(),
            ':first_name' => $model->getFirstName(),
            ':last_name' => $model->getLastName(),
            ':email_address' => $model->getEmailAddress(),
            ':title' => $model->getTitle(),
            ':date_of_birth' => $model->getDateOfBirth()->format("Y-m-d"),
            ':lender_reference' => $model->getLenderReference(),
            ':hosted_signing' => ($model->isHostedSigning()) ? 1:0,
            ':data_raw' => json_encode($model->getDAtaRaw())
        ]);

        return true;
    }

    /**
     * @param Signatory $model
     * @param bool $useReadReplica
     * @return Signatory
     * @throws ResourceNotFoundException
     */
    public function getSignatoryFromModel(Signatory $model, $useReadReplica = true): Signatory
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `first_name`, `last_name`, `email`, `title`, `date_of_birth`, `lender_reference`,
            `hosted_signing`, `data_raw`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_signatory`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_signatory', 'id', $model->getId());
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
    public function getAllSignatories(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `first_name`, `last_name`, `email`, `title`, `date_of_birth`, `lender_reference`,
            `hosted_signing`, `data_raw`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_signatory`
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
