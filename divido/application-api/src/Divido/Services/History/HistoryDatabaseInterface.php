<?php

namespace Divido\Services\History;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Ramsey\Uuid\Uuid;

/**
 * Class ApplicationHistoryDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class HistoryDatabaseInterface
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
     * @return History
     * @throws \Exception
     */
    public function mapToModel($data): History
    {
        $model = new History();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setType($data->type)
            ->setStatus($data->status)
            ->setUser($data->user)
            ->setSubject($data->subject)
            ->setText($data->text)
            ->setInternal($data->internal)
            ->setDate(new \DateTime($data->date))
            ->setIpAddress($data->ip_address)
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param History $model
     * @return mixed
     * @throws \Exception
     */
    public function createNewHistoryFromModel(History $model)
    {
        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application_history` 
          (
            `id`, `application_id`, `type`, `status`, `user`, `subject`, `text`, `internal`, `date`, `ip_address`
          )
          VALUES
	      (
	        :id, :application_id, :type, :status, :user, :subject, :text, :internal, :date, :ip_address
	      )');

        $statement->execute([
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':type' => $model->getType(),
            ':status' => $model->getStatus(),
            ':user' => $model->getUser(),
            ':subject' => $model->getSubject(),
            ':text' => $model->getText(),
            ':internal' => ($model->isInternal()) ? 1 : 0,
            ':date' => $model->getDate()->format("Y-m-d H:i:s"),
            ':ip_address' => $model->getIpAddress()
        ]);

        return $model;
    }

    /**
     * @param History $model
     * @return bool
     */
    public function updateHistoryFromModel(History $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_history` 
          SET 
            `subject` = :subject,
            `text` = :text, 
            `internal` = :internal
          WHERE id = :id 
          LIMIT 1');

        $statement->execute([
            ':id' => $model->getId(),
            ':subject' => $model->getStatus(),
            ':text' => $model->getText(),
            ':internal' => ($model->isInternal()) ? 1 : 0
        ]);

        return true;
    }

    /**
     * @param History $model
     * @param bool $useReadReplica
     * @return History
     * @throws ResourceNotFoundException
     */
    public function getHistoryFromModel(History $model, $useReadReplica = true): History
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `type`, `status`, `user`, `subject`, `text`, `internal`, `date`, `ip_address`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_history`
          WHERE `id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_history', 'id', $model->getId());
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
    public function getAllHistories(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            `id`, `application_id`, `type`, `status`, `user`, `subject`, `text`, `internal`, `date`, `ip_address`,
            `created_at`, COALESCE(`updated_at`, `created_at`) AS `updated_at` 
          FROM `application_history`
          WHERE `application_id` = :application_id
          ORDER BY `created_at` DESC');

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
