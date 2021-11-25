<?php

namespace Divido\Services\Application;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Helpers\Paginator\PaginatorHelper;
use Divido\Services\Tenant\Tenant;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;

/**
 * Class ApplicationDatabaseInterface
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationDatabaseInterface
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
     * @param \PDO $platformReadReplicaDb
     */
    function __construct(\PDO $platformMasterDb, \PDO $platformReadReplicaDb)
    {
        $this->platformMasterDb = $platformMasterDb;
        $this->platformReadReplicaDb = $platformReadReplicaDb;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function mapToModel($data)
    {
        $model = new Application();
        $model->setId($data->id)
            ->setToken($data->token)
            ->setTenantId($data->platform_environment_id)
            ->setBranchId($data->branch_id)
            ->setApplicationSubmissionId($data->application_submission_id)
            ->setCountryCode($data->country_id)
            ->setCurrencyCode($data->currency_id)
            ->setLanguageCode($data->language_id)
            ->setMerchantId($data->merchant_id)
            ->setCustomerId($data->customer_id)
            ->setMerchantChannelId($data->merchant_channel_id)
            ->setMerchantApiKeyId($data->merchant_api_key_id)
            ->setMerchantUserId($data->merchant_user_id)
            ->setFinalised($data->finalised ?? false)
            ->setFinalisationRequired($data->finalisation_required ?? false)
            ->setStatus($data->status)
            ->setPurchasePrice($data->purchase_price ?? 0)
            ->setDepositAmount($data->deposit_amount ?? 0)
            ->setDepositStatus($data->deposit_status)
            ->setLenderFee($data->lender_fee)
            ->setLenderFeeReportedDate(($data->lender_fee_reported_date) ? new \DateTime($data->lender_fee_reported_date) : null)
            ->setFormData((is_object(json_decode($data->form_data))) ? json_decode($data->form_data) : json_decode("{}"))
            ->setApplicants((is_object(json_decode($data->applicants))) ? json_decode($data->applicants) : json_decode("{}"))
            ->setProductData((is_array(json_decode($data->product_data, 2))) ? json_decode($data->product_data, 2) : [])
            ->setMetadata((is_object(json_decode($data->metadata))) ? json_decode($data->metadata) : json_decode("{}"))
            ->setCommission($data->commission)
            ->setPartnerCommission($data->partner_commission)
            ->setMerchantReference($data->merchant_reference)
            ->setMerchantResponseUrl($data->merchant_response_url)
            ->setMerchantCheckoutUrl($data->merchant_checkout_url)
            ->setMerchantRedirectUrl($data->merchant_redirect_url)
            ->setFinanceSettings((is_object(json_decode($data->finance_settings))) ? json_decode($data->finance_settings) : json_decode("{}"))
            ->setTerms((is_object(json_decode($data->terms))) ? json_decode($data->terms) : json_decode("{}"))
            ->setMerchantFinanceOptionId((($data->merchant_finance_option_id) ? $data->merchant_finance_option_id : $data->merchant_finance_id))
            ->setAvailableFinanceOptions((is_array(json_decode($data->available_finance_options, 2))) ? json_decode($data->available_finance_options, 2) : [])
            ->setCancelledAmount($data->cancelled_amount ?? 0)
            ->setCancelledAmountTotal($data->cancelled_amount_total ?? 0)
            ->setActivatedAmount($data->activated_amount ?? 0)
            ->setActivatedAmountTotal($data->activatable_amount_total ?? 0)
            ->setRefundedAmount($data->refunded_amount ?? 0)
            ->setRefundedAmountTotal($data->refunded_amount_total ?? 0)
            ->setSignerCollectionId($data->signer_collection_id)
            ->setCreatedAt(new \DateTime($data->created_at))
            ->setUpdatedAt(new \DateTime($data->updated_at));

        $this->mapAmountsFromTerms($model);

        return $model;
    }

    /**
     * @param Application $model
     * @return mixed
     * @throws \Exception
     */
    public function createNewApplicationFromModel(Application $model)
    {
        $statement = $this->platformMasterDb->prepare('SELECT 
            mf.`id`
          FROM `merchant_finance` AS mf
          
          WHERE mf.`id` = :id AND mf.`deleted_at` IS NULL');

        $statement->execute([
            ':id' => $model->getMerchantFinanceOptionId(),
        ]);

        $data = $statement->fetch();
        $merchantFinanceId = $data->id;

        $statement = $this->platformMasterDb->prepare('
          INSERT INTO `application` 
          (
            `id`, `token`, `platform_environment_id`, `branch_id`, `country_id`, `currency_id`, `language_id`, `merchant_id`, 
            `merchant_channel_id`, `merchant_api_key_id`, `merchant_user_id`, `finalised`, `finalisation_required`, 
            `status`, `purchase_price`, `deposit_amount`, `deposit_status`, `lender_fee`, `form_data`, `applicants`, 
            `product_data`, `metadata`, `commission`, `partner_commission`, `merchant_reference`, `merchant_response_url`, 
            `merchant_checkout_url`, `merchant_redirect_url`, `finance_settings`, `merchant_finance_option_id`, `available_finance_options`,
            `deposit_reference`, `activation_status`, `merchant_finance_id`, `lender_loan_reference`
            
            )
          VALUES
	      (
	        :id, :token, :tenant_id, :branch_id, :country_id, :currency_id, :language_id, :merchant_id, 
	        :merchant_channel_id, :merchant_api_key_id, :merchant_user_id, :finalised, :finalisation_required, 
	        :status, :purchase_price, :deposit_amount, :deposit_status, :lender_fee, :form_data, :applicants, 
	        :product_data, :metadata, :commission, :partner_commission, :merchant_reference, :merchant_response_url,
	         :merchant_checkout_url, :merchant_redirect_url,  :finance_settings, :merchant_finance_option_id, :available_finance_options,
	         :deposit_reference, :activation_status, :merchant_finance_id, :lender_loan_reference
	      )');

        $data = [
            ':id' => $model->getId(),
            ':token' => $model->getToken(),
            ':tenant_id' => $model->getTenantId(),
            ':branch_id' => $model->getBranchId(),
            ':country_id' => $model->getCountryCode(),
            ':currency_id' => $model->getCurrencyCode(),
            ':language_id' => $model->getLanguageCode(),
            ':merchant_id' => $model->getMerchantId(),
            ':merchant_channel_id' => $model->getMerchantChannelId(),
            ':merchant_api_key_id' => $model->getMerchantApiKeyId(),
            ':merchant_user_id' => $model->getMerchantUserId(),
            ':finalised' => ($model->isFinalised()) ? 1 : 0,
            ':finalisation_required' => ($model->isFinalisationRequired()) ? 1 : 0,
            ':status' => $model->getStatus(),
            ':purchase_price' => $model->getPurchasePrice(),
            ':deposit_amount' => $model->getDepositAmount(),
            ':deposit_status' => $model->getDepositStatus(),
            ':lender_fee' => $model->getLenderFee(),
            ':form_data' => json_encode($model->getFormData()),
            ':applicants' => json_encode($model->getApplicants()),
            ':product_data' => json_encode($model->getProductData()),
            ':metadata' => json_encode($model->getMetadata()),
            ':commission' => $model->getCommission(),
            ':partner_commission' => $model->getPartnerCommission(),
            ':merchant_reference' => $model->getMerchantReference(),
            ':merchant_response_url' => $model->getMerchantResponseUrl(),
            ':merchant_checkout_url' => $model->getMerchantCheckoutUrl(),
            ':merchant_redirect_url' => $model->getMerchantRedirectUrl(),
            ':finance_settings' => json_encode($model->getFinanceSettings()),
            ':merchant_finance_option_id' => $model->getMerchantFinanceOptionId(),
            ':available_finance_options' => (count($model->getAvailableFinanceOptions()) > 0) ? json_encode((array)implode(",", $model->getAvailableFinanceOptions())) : json_encode([]),
            ':deposit_reference' => '',
            ':activation_status' => '',
            ':merchant_finance_id' => $merchantFinanceId,
            ':lender_loan_reference' => 'application-api',
        ];

        $statement->execute($data);

        $this->logger->info(sprintf('Created application record in DB for application: %s', $model->getId()), [
            'query_data' => $data,
        ]);

        $statement = $this->platformMasterDb->prepare('INSERT INTO `application_term` (`id`, `application_id`, `terms`, `invalidated_at`) VALUES (:id, :application_id, :terms, :invalidated_at)');
        $data = [
            ':id' => Uuid::uuid4()->toString(),
            ':application_id' => $model->getId(),
            ':terms' => json_encode($model->getTerms()),
            ':invalidated_at' => null,
        ];

        $statement->execute($data);

        $this->logger->info(sprintf('Created application term record in DB for application: %s', $model->getId()), [
            'query_data' => $data,
        ]);

        return $model->getId();
    }

    /**
     * @param Tenant $platformEnvironment
     * @param Application $model
     * @param bool $useReadReplica
     * @return Application
     * @throws ResourceNotFoundException
     */
    public function getApplicationFromModel(Tenant $platformEnvironment, Application $model, $useReadReplica = true)
    {

        if ($model->isOnlyTokenSet()) {
            $whereName = 'token';
            $whereKey = $model->getToken();
        } else {
            $whereName = 'id';
            $whereKey = $model->getId();
        }

        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $db->prepare('SELECT 
            a.`id`,
            a.`token`,
            a.`platform_environment_id`,
            a.`branch_id`,
            a.`application_submission_id`,
            a.`country_id`,
            a.`currency_id`,
            a.`language_id`,
            a.`merchant_id`,
            a.`customer_id`,
            a.`merchant_finance_id`,
            a.`merchant_channel_id`,
            a.`merchant_api_key_id`,
            a.`merchant_user_id`,
            a.`finalised`,
            a.`finalisation_required`,
            a.`status`,
            a.`purchase_price`,
            a.`deposit_amount`,
            a.`deposit_status`,
            a.`lender_fee`,
            a.`lender_fee_reported_date`,
            a.`form_data`,
            a.`applicants`,
            a.`product_data`,
            a.`metadata`,
            a.`commission`,
            a.`partner_commission`,
            a.`merchant_reference`,
            a.`merchant_response_url`,
            a.`merchant_checkout_url`,
            a.`merchant_redirect_url`,
            a.`finance_settings`,
            a.`merchant_finance_option_id`,
            a.`available_finance_options`,
            t.`terms`,
            s.`collection_id` as `signer_collection_id`,
            
            (SELECT SUM(ac.`amount`) FROM `application_cancellation` AS `ac` WHERE
            ac.`application_id` = a.`id`) AS `cancelled_amount_total`,
            (SELECT SUM(ac.`amount`) FROM `application_cancellation` AS `ac` WHERE
            ac.`application_id` = `a`.`id` AND (ac.`status` = "CANCELLED" OR ac.`status` = "AWAITING-CANCELLATION")) AS `cancelled_amount`,
            
            (SELECT SUM(aa.`amount`) FROM `application_activation` AS `aa` WHERE
            aa.`application_id` = a.`id`) AS `activated_amount_total`,
            (SELECT SUM(aa.`amount`) FROM `application_activation` AS `aa` WHERE
            aa.`application_id` = `a`.`id` AND (aa.`status` = "ACTIVATED" OR aa.`status` = "AWAITING-ACTIVATION")) AS `activated_amount`,
            
            (SELECT SUM(ar.`amount`) FROM `application_refund` AS `ar` WHERE
            ar.`application_id` = a.`id`) AS `refunded_amount_total`,
            (SELECT SUM(ar.`amount`) FROM `application_refund` AS `ar` WHERE
            ar.`application_id` = `a`.`id` AND ar.`status` = "REFUNDED") AS `refunded_amount`,
            
            a.`created_at`, COALESCE(a.`updated_at`, a.`created_at`) AS `updated_at`,

            l.`settings` AS `lender_settings`
          FROM `application` AS a
          LEFT JOIN `application_term` as t ON (t.`application_id` = a.`id` AND `invalidated_at` IS NULL)
          LEFT JOIN `application_signer_collection` s ON (s.`application_id` = a.`id` AND s.`deleted_at` IS NULL)
          LEFT JOIN `application_submission` sub ON (sub.`id` = a.`application_submission_id`)
          LEFT JOIN `lender` l ON (l.`id` = sub.`lender_id`)
          WHERE a.`status` != :status 
            AND a.`' . $whereName . '` = :where');

        $statement->execute([
            ':status' => 'DELETED',
            ':where' => $whereKey
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('application', $whereName, $whereKey);
        }

        $data = $statement->fetch();

        $model = $this->mapToModel($data);

        $model->setApplicationFormUrl($platformEnvironment, json_decode($data->lender_settings, true) ?? []);

        return $model;

    }

    /**
     * @param Tenant $tenant
     * @param PaginatorHelper $paginator
     * @param bool $useReadReplica
     * @return array
     * @throws \Exception
     */
    public function getAllApplications(Tenant $tenant, PaginatorHelper $paginator, $useReadReplica = true)
    {
        $db = ($useReadReplica) ? $this->platformReadReplicaDb : $this->platformMasterDb;

        $statement = $paginator->getPrepareStatement($db, [
            'select' => 'a.`id`, a.`token`, a.`platform_environment_id`,  a.`branch_id`, a.`application_submission_id`, a.`country_id`, a.`currency_id`,
                a.`language_id`, a.`merchant_id`, a.`customer_id`, a.`merchant_finance_id`, a.`merchant_channel_id`,
                a.`merchant_api_key_id`, a.`merchant_user_id`, a.`finalised`, a.`finalisation_required`, a.`status`,
                a.`purchase_price`, a.`deposit_amount`, a.`deposit_status`, a.`lender_fee`, a.`lender_fee_reported_date`,
                a.`form_data`, a.`applicants`, a.`product_data`, a.`metadata`, a.`commission`, a.`partner_commission`,
                a.`merchant_reference`, a.`merchant_response_url`, a.`merchant_checkout_url`, a.`merchant_redirect_url`,
                a.`finance_settings`, a.`merchant_finance_option_id`, a.`available_finance_options`, t.`terms`,
                a.`created_at`, COALESCE(a.`updated_at`, a.`created_at`) AS `updated_at`,
                l.`settings` AS `lender_settings`',
            'from' => '`application` AS a
                LEFT JOIN `application_term` as t ON (t.`application_id` = a.`id` AND `invalidated_at` IS NULL)
                LEFT JOIN `application_submission` sub ON (sub.`id` = a.`application_submission_id`)
                LEFT JOIN `lender` l ON (l.`id` = sub.`lender_id`)',
            'where' => ['a.`status` != :status_not
                AND a.`platform_environment_id` = :tenant_id'],
            'params' => [':status_not' => 'DELETED', ':tenant_id' => $tenant->getId()],
        ]);

        $rows = $statement->fetchAll();

        $models = [];

        foreach ($rows as $data) {
            $model = $this->mapToModel($data);
            $model->setApplicationFormUrl($tenant, json_decode($data->lender_settings, true) ?? []);

            $models[] = $model;
        }

        return $models;

    }

    /**
     * @param Application $model
     * @return bool
     */
    public function updateApplicationFromModel(Application $model)
    {
        $statement = $this->platformMasterDb->prepare('
          UPDATE `application` 
          SET 
            `status` = :status,
            `application_submission_id` = :application_submission_id,
            `merchant_finance_option_id` = :merchant_finance_option_id,
            `finalised` = :finalised, 
            `finalisation_required` = :finalisation_required,
            `purchase_price` = :purchase_price,
            `deposit_amount` = :deposit_amount, 
            `deposit_status` = :deposit_status,
            `form_data` = :form_data,
            `applicants` = :applicants,
            `product_data` = :product_data,
            `commission` = :commission,
            `partner_commission` = :partner_commission,
            `lender_fee` = :lender_fee,
            `lender_fee_reported_date` = :lender_fee_reported_date,
            `metadata` = :metadata,
            `merchant_reference` = :merchant_reference,
            `merchant_response_url` = :merchant_response_url,
            `merchant_checkout_url` = :merchant_checkout_url,
            `merchant_redirect_url` = :merchant_redirect_url,
            `available_finance_options` = :available_finance_options,
            `merchant_channel_id` = :merchant_channel_id
          WHERE status != \'DELETED\'
            AND id = :id
          LIMIT 1');

        $data = [
            ':id' => $model->getId(),
            ':application_submission_id' => $model->getApplicationSubmissionId(),
            ':merchant_finance_option_id' => $model->getMerchantFinanceOptionId(),
            ':status' => $model->getStatus(),
            ':finalised' => ($model->isFinalised()) ? 1 : 0,
            ':finalisation_required' => ($model->isFinalisationRequired()) ? 1 : 0,
            ':purchase_price' => $model->getPurchasePrice(),
            ':deposit_amount' => $model->getDepositAmount(),
            ':deposit_status' => $model->getDepositStatus(),
            ':form_data' => json_encode($model->getFormData()),
            ':applicants' => json_encode($model->getApplicants()),
            ':product_data' => json_encode($model->getProductData()),
            ':commission' => $model->getCommission(),
            ':partner_commission' => $model->getPartnerCommission(),
            ':lender_fee' => $model->getLenderFee(),
            ':lender_fee_reported_date' => (is_object($model->getLenderFeeReportedDate())) ? $model->getLenderFeeReportedDate()->format('Y-m-d') : null,
            ':metadata' => json_encode($model->getMetadata()),
            ':merchant_reference' => $model->getMerchantReference(),
            ':merchant_response_url' => $model->getMerchantResponseUrl(),
            ':merchant_checkout_url' => $model->getMerchantCheckoutUrl(),
            ':merchant_redirect_url' => $model->getMerchantRedirectUrl(),
            ':available_finance_options' => json_encode($model->getAvailableFinanceOptions()),
            ':merchant_channel_id' => $model->getMerchantChannelId(),
        ];

        $statement->execute($data);

        $this->logger->info(
            sprintf('Updated application record in DB for application: %s', $model->getId()),
            [
                'query_data' => $data,
            ]
        );

        return true;
    }

    /**
     * @param Application $model
     * @return bool
     * @throws \Exception
     */
    public function deleteApplicationFromModel(Application $model)
    {
        $statement = $this->platformMasterDb->prepare("UPDATE `application` SET `deleted_at` = :deleted_at WHERE 
        `id` = :id");

        $deletedAt = new \DateTime();

        $statement->execute([
            ':deleted_at' => $deletedAt->format("Y-m-d H:i:s"),
            ':id' => $model->getId()
        ]);

        return true;
    }

    /**
     * Override the `purchase_price` and `deposit_amount` fields on the model with
     * values from the `terms->amounts` field.
     *
     * The values in this field are demonstrably more accurate than those in the
     * `application` table.
     *
     * @param Application $model
     * @return Application
     */
    protected function mapAmountsFromTerms(Application $model): Application
    {
        $purchasePrice = $model->getTerms()->amounts->purchase_amount ?? $model->getPurchasePrice();
        $model->setPurchasePrice($purchasePrice);

        $depositAmount = $model->getTerms()->amounts->deposit_amount ?? $model->getDepositAmount();
        $model->setDepositAmount($depositAmount);

        return $model;
    }
}
