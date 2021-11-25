<?php

namespace Divido\Helpers;

use Divido\Services\Application\Application;

/**
 * Class ApplicationObjectBuilder
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationObjectBuilder
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
     * @param Application $model
     * @param bool $useReadReplica
     * @return object
     * @throws \Exception
     */
    public function getObject(Application $model, $useReadReplica = true): object
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;
        $statement = $db->prepare('SELECT
            `a`.`id`,
            `a`.`token`,
            `a`.`application_submission_id` as `application_submission_id`,
            `a`.`platform_environment_id` as `tenant_id`,
            `a`.`available_finance_options`,
            `a`.`country_id` AS `country_code`,
            `a`.`language_id` AS `language_code`,
            `a`.`currency_id` AS `currency_code`,
            `mc`.`id` AS `channel_id`,
            `mc`.`name` AS `channel_name`,
            `mc`.`type` AS `channel_type`,
            `a`.`status` AS `status`,
            (
                SELECT GROUP_CONCAT(`ah`.`status`) FROM `application_history` AS `ah`
                WHERE `ah`.`type` = "status"
                    AND `ah`.`application_id` = `a`.`id`
            ) AS `statuses`,
            `a`.`deposit_status` AS `deposit_status`,
            `a`.`deposit_amount` AS `deposit_amount`,
            `at`.`terms` AS `terms`,
            `a`.`product_data`,
            `a`.`metadata`,
            `m`.`id` AS `merchant_id`,
            `m`.`name` AS `merchant_name`,
            `m`.`settings` AS `merchant_settings_generic`,
            `m`.`layout_css` AS `merchant_settings_layout_css`,
            `m`.`layout_html` AS `merchant_settings_layout_html`,
            `m`.`layout_styling` AS `merchant_settings_layout_styling`,
            `m`.`layout_logo` AS `merchant_settings_layout_logo`,
            `m`.`theme_id` AS `merchant_settings_layout_theme_id`,
            `m`.`shared_secret` AS `merchant_shared_secret`,
            `m`.`decision_rule_template_id` AS `merchant_decision_rule_template_id`,
            `a`.`applicants`,
            `a`.`merchant_reference` AS `merchant_reference`,
            `a`.`merchant_checkout_url` AS `checkout_url`,
            `a`.`merchant_redirect_url` AS `redirect_url`,
            `a`.`created_at`, COALESCE(`a`.`updated_at`, `a`.`created_at`) AS `updated_at`
        FROM `application` AS `a`
        LEFT JOIN `application_term` as `at` ON (`at`.`application_id` = `a`.`id` AND `invalidated_at` IS NULL)
        LEFT JOIN `merchant` AS `m` ON (`m`.`id` = `a`.`merchant_id`)
        LEFT JOIN `merchant_channel` AS `mc` ON (`mc`.`id` = `a`.`merchant_channel_id`)
        WHERE `a`.`id` = :id');

        $statement->execute([':id' => $model->getId()]);

        $application = $statement->fetch();

        return (object) [
            'id' => $application->id,
            'token' => $application->token,
            'tenant_id' => $application->tenant_id,
            'application_submission_id' => $application->application_submission_id,
            'finance' => (object) [
                'alternative_offers' => (array) [],
                'available_finance_options' => json_decode($application->available_finance_options, 1),
            ],
            'payment_provider' => (object) [
                'id' => null,
                'name' => '',
                'app_name' => '',
                'settings' => (object) [
                    'generic' => (object) [],
                    'merchant' => (object) [
                        'publishable_key' => null,
                    ]
                ]
            ],
            'country' => (object) [
                'code' => $application->country_code
            ],
            'language' => (object) [
                'code' => $application->language_code
            ],
            'currency' => (object) [
                'code' => $application->currency_code
            ],
            'channel' => (object) [
                'id' => $application->channel_id,
                'name' => $application->channel_name,
                'type' => $application->channel_type
            ],
            'status' => $model->getStatus(),
            'statuses' => (array) explode(",", $application->statuses),
            'deposit' => (object) [
                'status' => $model->getDepositStatus(),
                'amount' => $model->getDepositAmount()
            ],
            'terms' => json_decode($application->terms, 0),
            'order' => (object) [
                'product_data' => json_decode($application->product_data, 0)
            ],
            'metadata' => (object) json_decode($application->metadata, 0),
            'merchant' => (object) [
                'id' => $application->merchant_id,
                'name' => $application->merchant_name,
                'decision_rule_template_id' => $application->merchant_decision_rule_template_id,
                'settings' => [
                    'shared_secret' => $application->merchant_shared_secret,
                    'generic' => $application->merchant_settings_generic,
                    'layout' => [
                        'css' => $application->merchant_settings_layout_css,
                        'html' => $application->merchant_settings_layout_html,
                        'styling' => $application->merchant_settings_layout_styling,
                        'logo' => $application->merchant_settings_layout_logo,
                        'theme_id' => $application->merchant_settings_layout_theme_id
                    ],
                ],
            ],
            'applicants' => (object) json_decode($application->applicants, 0),
            'signatories' => (array) $this->getSignatories($model),
            'merchant_reference' => $application->merchant_reference,
            'cancelable_amount' => $model->getCancelableAmount(),
            'cancelled_amount' => $model->getCancelledAmount(),
            'activatable_amount' => $model->getActivatableAmount(),
            'activated_amount' => $model->getActivatedAmount(),
            'refundable_amount' => $model->getRefundableAmount(),
            'refunded_amount' => $model->getRefundedAmount(),
            'urls' => (object) [
                'checkout_url' => $application->checkout_url,
                'redirect_url' => $application->redirect_url,
                'application_form_url' => $model->getApplicationFormUrl()
            ],
            'created_at' => (new \DateTime($application->created_at))->format("c"),
            'updated_at' => (new \DateTime($application->updated_at))->format("c"),
        ];
    }

    /**
     * @param Application $model
     * @return array
     */
    public function getSignatories(Application $model): array
    {
        return [];
    }
}
