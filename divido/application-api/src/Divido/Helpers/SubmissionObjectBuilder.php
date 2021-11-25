<?php

namespace Divido\Helpers;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\Services\Application\Application;
use Divido\Services\Submission\Submission;

/**
 * Class SubmissionObjectBuilder
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class SubmissionObjectBuilder
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
     * MerchantPortalService constructor.
     * @param \PDO $platformMasterDb
     * @param \PDO $platformReadReplicaDb
     */
    function __construct(\PDO $platformMasterDb, \PDO $platformReadReplicaDb)
    {
        $this->platformMasterDb = $platformMasterDb;
        $this->platformReadReplicaDb = $platformReadReplicaDb;
    }

    /**
     * @param Application $application
     * @param null $applicationSubmission
     * @param bool $useReadReplica
     * @return object
     * @throws ApplicationSubmissionErrorException
     */
    public function getSubmission(Application $application, $applicationSubmission = null, $useReadReplica = true): object
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        if ($applicationSubmission instanceof Submission) {
            $id = $applicationSubmission->getId();
        } else {
            if (!($id = $application->getApplicationSubmissionId())) {
                $submissions = $this->getAllSubmissions($application);
                if (count($submissions) > 0) {
                    $id = $submissions[0]->id;
                }

                if (empty($id)) {
                    throw new ApplicationSubmissionErrorException('No application submission exists');
                }
            }
        }

        $statement = $db->prepare('SELECT
                    `as`.`id`,
                    `a`.`id` AS `application_id`,
                    `as`.`lender_id` as `old_lender_id`,
                    `mfp`.`lender_id`,
                    `as`.`application_alternative_offer_id`,
                    `as`.`decline_referred`,
                    `as`.`order`,
                    `as`.`merchant_finance_plan_id`,
                    `as`.`status`,
                    `as`.`lender_reference`,
                    `as`.`lender_loan_reference`,
                    `as`.`lender_status`,
                    `as`.`lender_data`,
                    `mfp`.`lender_code`,
                    `mfp`.`decision_rule_template_id`,
                    `l`.`id` AS `lender_id`,
                    `l`.`name` AS `lender_name`,
                    `l`.`app_name` AS `lender_app_name`,
                    `l`.`settings` AS `lender_settings_generic`,
                    `ml`.`settings` AS `lender_settings_merchant`,
                    `as`.`created_at`, COALESCE(`as`.`updated_at`, `as`.`created_at`) AS `updated_at`
            FROM application_submission AS `as` 
            INNER JOIN `application` AS `a` ON (`a`.`id` = `as`.`application_id`)
            LEFT JOIN `merchant_finance_plan` AS `mfp` ON (`mfp`.`id` = `as`.`merchant_finance_plan_id`)
            LEFT JOIN `lender` AS `l` ON (`l`.`id` = `mfp`.`lender_id`)
            LEFT JOIN `merchant_lender` AS `ml` ON (`ml`.`merchant_id` = `a`.`merchant_id` AND `ml`.`lender_id` = `l`.`id`)
            WHERE `as`.`id` = :id');

        $statement->execute([':id' => $id]);

        $submission = $statement->fetch();

        return (object) [
            'id' => $submission->id,
            'order' => (int) $submission->order,
            'decline_referred' => ($submission->decline_referred) ? true : false,
            'lender' => (object) [
                'id' => $submission->lender_id,
                'name' => $submission->lender_name,
                'app_name' => $submission->lender_app_name,
                'settings' => [
                    'generic' => json_decode($submission->lender_settings_generic, 0),
                    'merchant' => json_decode($submission->lender_settings_merchant, 0)
                ]
            ],
            'application_alternative_offer_id' => $submission->application_alternative_offer_id,
            'merchant_finance_plan' => [
                'id' => $submission->merchant_finance_plan_id,
                'lender_code' => $submission->lender_code,
                'decision_rule_template_id' => $submission->decision_rule_template_id
            ],
            'status' => $submission->status,
            'lender_reference' => $submission->lender_reference,
            'lender_loan_reference' => $submission->lender_loan_reference,
            'lender_status' => $submission->lender_status,
            'lender_data' => (object) json_decode($submission->lender_data),
            'created_at' => (new \DateTime($submission->created_at))->format("c"),
            'updated_at' => (new \DateTime($submission->updated_at))->format("c"),
        ];
    }

    /**
     * @param Application $application
     * @param bool $useReadReplica
     * @return array
     * @throws \Exception
     */
    public function getAllSubmissions(Application $application, $useReadReplica = true): array
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT
                    `as`.`id`,
                    `a`.`id` AS `application_id`,
                    `as`.`lender_id` as `old_lender_id`,
                    `mfp`.`lender_id`,
                    `as`.`application_alternative_offer_id`,
                    `as`.`decline_referred`,
                    `as`.`order`,
                    `as`.`merchant_finance_plan_id`,
                    `as`.`status`,
                    `as`.`lender_reference`,
                    `as`.`lender_loan_reference`,
                    `as`.`lender_status`,
                    `as`.`lender_data`,
                    `mfp`.`lender_code`,
                    `mfp`.`decision_rule_template_id`,
                    `l`.`id` AS `lender_id`,
                    `l`.`name` AS `lender_name`,
                    `l`.`app_name` AS `lender_app_name`,
                    `l`.`settings` AS `lender_settings_generic`,
                    `ml`.`settings` AS `lender_settings_merchant`,
                    `as`.`created_at`, COALESCE(`as`.`updated_at`, `as`.`created_at`) AS `updated_at`
            FROM application_submission AS `as` 
            INNER JOIN `application` AS `a` ON (`a`.`id` = `as`.`application_id`)
            LEFT JOIN `merchant_finance_plan` AS `mfp` ON (`mfp`.`id` = `as`.`merchant_finance_plan_id`)
            LEFT JOIN `lender` AS `l` ON (`l`.`id` = `mfp`.`lender_id`)
            LEFT JOIN `merchant_lender` AS `ml` ON (`ml`.`merchant_id` = `a`.`merchant_id` AND `ml`.`lender_id` = `l`.`id`)
            WHERE `as`.`application_id` = :id');

        $statement->execute([':id' => $application->getId()]);

        $rows = $statement->fetchAll();

        $submissions = [];

        foreach ($rows as $submission) {
            $submissions[] = (object) [
                'id' => $submission->id,
                'order' => (int) $submission->order,
                'decline_referred' => ($submission->decline_referred) ? true : false,
                'lender' => (object) [
                    'id' => $submission->lender_id,
                    'name' => $submission->lender_name,
                    'app_name' => $submission->lender_app_name,
                    'settings' => [
                        'generic' => json_decode($submission->lender_settings_generic, 0),
                        'merchant' => json_decode($submission->lender_settings_merchant, 0),
                    ]
                ],
                'application_alternative_offer_id' => $submission->application_alternative_offer_id,
                'merchant_finance_plan' => [
                    'id' => $submission->merchant_finance_plan_id,
                    'lender_code' => $submission->lender_code,
                    'decision_rule_template_id' => $submission->decision_rule_template_id
                ],
                'status' => $submission->status,
                'lender_reference' => $submission->lender_reference,
                'lender_loan_reference' => $submission->lender_loan_reference,
                'lender_status' => $submission->lender_status,
                'lender_data' => (object) json_decode($submission->lender_data),
                'created_at' => (new \DateTime($submission->created_at))->format("c"),
                'updated_at' => (new \DateTime($submission->updated_at))->format("c"),
            ];
        }

        return $submissions;
    }
}
