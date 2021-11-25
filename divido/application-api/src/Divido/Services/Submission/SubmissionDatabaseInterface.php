<?php

namespace Divido\Services\Submission;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Psr\Log\LoggerAwareTrait;

/**
 * Class SubmissionDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class SubmissionDatabaseInterface
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
     * @return Submission
     * @throws \Exception
     */
    public function mapToModel($data): Submission
    {
        $model = new Submission();
        $model->setId($data->id)
            ->setApplicationId($data->application_id)
            ->setOrder($data->order ?? 1)
            ->setDeclineReferred(($data->decline_referred) ? 1 : 0)
            ->setLenderId($data->lender_id)
            ->setApplicationAlternativeOfferId($data->application_alternative_offer_id)
            ->setMerchantFinancePlanId($data->merchant_finance_plan_id)
            ->setStatus($data->status)
            ->setLenderReference($data->lender_reference)
            ->setLenderLoanReference($data->lender_loan_reference)
            ->setLenderStatus($data->lender_status)
            ->setLenderData((object) json_decode($data->lender_data, 0))
            ->setLenderCode($data->lender_code ?? "")
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        return $model;
    }

    /**
     * @param string $lenderId
     * @return bool
     */
    private function doesLenderExist(string $lenderId) : bool
    {
        $statement = $this->platformMasterDb->prepare(
            'SELECT id FROM `lender` WHERE `lender`.`id` = :id AND `lender`.`deleted_at` IS NULL'
        );

        $statement->execute(
            [
                'id' => $lenderId
            ]
        );

        if ($statement->rowCount() === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param Submission $model
     * @return mixed
     * @throws ResourceNotFoundException
     * @throws \Exception
     */
    public function createNewSubmissionFromModel(Submission $model)
    {
        // Check if there is a lender
        if(!$this->doesLenderExist($model->getLenderId())){
            $this->logger->error(
                'Could not create submission with lender that does not exist',
                [
                    'lender_id' => $model->getLenderId(),
                    'application_id' => $model->getApplicationId(),
                ]
            );

            throw new ResourceNotFoundException('lender', 'id', $model->getLenderId());
        }

        $statement = $this->platformMasterDb->prepare('
            INSERT INTO `application_submission` 
                (`id`, `application_id`,`order`,`decline_referred`,`lender_id`,`application_alternative_offer_id`,
                `merchant_finance_plan_id`,`status`,`lender_reference`,`lender_loan_reference`,`lender_status`,`lender_data`)
            VALUES
                (:id,:application_id,:order,:decline_referred,:lender_id,:application_alternative_offer_id,
                :merchant_finance_plan_id,:status,:lender_reference,:lender_loan_reference,:lender_status,:lender_data)');

        $statement->execute([
            ':id' => $model->getId(),
            ':application_id' => $model->getApplicationId(),
            ':order' => $model->getOrder(),
            ':decline_referred' => ($model->isDeclineReferred()) ? 1 : 0,
            ':lender_id' => $model->getLenderId(),
            ':application_alternative_offer_id' => $model->getApplicationAlternativeOfferId(),
            ':merchant_finance_plan_id' => $model->getMerchantFinancePlanId(),
            ':status' => $model->getStatus(),
            ':lender_reference' => $model->getLenderReference(),
            ':lender_loan_reference' => $model->getLenderLoanReference(),
            ':lender_status' => $model->getLenderStatus(),
            ':lender_data' => json_encode((object) $model->getLenderData()),
        ]);

        return $model->getId();
    }

    /**
     * @param Submission $model
     * @return bool
     */
    public function updateSubmissionFromModel(Submission $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application_submission` SET
            `order` = :order,
            `application_alternative_offer_id` = :application_alternative_offer_id,
            `merchant_finance_plan_id` = :merchant_finance_plan_id,
            `status` = :status,
            `decline_referred` = :decline_referred,
            `lender_reference` = :lender_reference,
            `lender_loan_reference` = :lender_loan_reference,
            `lender_status` = :lender_status,
            `lender_data` = :lender_data
          WHERE `id` = :id');

        $statement->execute([
            ':order' => $model->getOrder(),
            ':application_alternative_offer_id' => $model->getApplicationAlternativeOfferId(),
            ':merchant_finance_plan_id' => $model->getMerchantFinancePlanId(),
            ':status' => $model->getStatus(),
            ':decline_referred' => ($model->isDeclineReferred()) ? 1 : 0,
            ':lender_reference' => $model->getLenderReference(),
            ':lender_loan_reference' => $model->getLenderLoanReference(),
            ':lender_status' => $model->getLenderStatus(),
            ':lender_data' => json_encode($model->getLenderData()),
            ':id' => $model->getId()
        ]);

        $this->logger->debug('update application_submission table', [
            'id' => $model->getId(),
            'lender_data' => $model->getLenderData(),
            'lender_status' => $model->getLenderStatus(),
            'lender_reference' => $model->getLenderReference(),
            'lender_loan_reference' => $model->getLenderLoanReference(),
        ]);

        return true;
    }

    /**
     * @param Submission $model
     * @param bool $useReadReplica
     * @return Submission
     * @throws ResourceNotFoundException
     */
    public function getSubmissionFromModel(Submission $model, $useReadReplica = true): Submission
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('
          SELECT 
            `application_submission`.`id`,`application_submission`.`application_id`, `application_submission`.`order`,
            `application_submission`.`lender_id`, `application_submission`.`decline_referred`,
            `application_submission`.`application_alternative_offer_id`,`application_submission`.`merchant_finance_plan_id`,
            `application_submission`.`status`, `application_submission`.`lender_reference`,
            `application_submission`.`lender_loan_reference`, `application_submission`.`lender_status`,
            `application_submission`.`lender_data`,
            COALESCE(`application_submission`.`updated_at`, `application_submission`.`created_at`) AS `updated_at`
          FROM `application_submission`
          WHERE `application_submission`.`id` = :id');

        $statement->execute([
            ':id' => $model->getId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application_submission', 'id', $model->getId());
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
    public function getAllSubmissions(Application $application, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('
          SELECT 
            `application_submission`.`id`,`application_submission`.`application_id`, `application_submission`.`order`,
            `application_submission`.`lender_id`, `application_submission`.`decline_referred`,
            `application_submission`.`application_alternative_offer_id`,`application_submission`.`merchant_finance_plan_id`,
            `application_submission`.`status`, `application_submission`.`lender_reference`,
            `application_submission`.`lender_loan_reference`, `application_submission`.`lender_status`,
            `application_submission`.`lender_data`,
            COALESCE(`application_submission`.`updated_at`, `application_submission`.`created_at`) AS `updated_at`
          FROM `application_submission`
          WHERE `application_submission`.`application_id` = :application_id
          ORDER BY `application_submission`.`order`');

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
