<?php

namespace Divido\Services\Application;

use Divido\ApiExceptions\ApplicationInputInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\IndexRateSdk\Client as IndexRateApiSdk;
use Divido\IndexRateSdk\Exception\RateNotFoundException;
use Divido\Proxies\Calculator;
use Divido\Proxies\Validation;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ApplicationCreationService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationCreationService
{
    use LoggerAwareTrait;

    /** @var \PDO $pdo */
    private $pdo;

    /** @var Calculator $calculatorProxy */
    private $calculatorProxy;

    /** @var Validation */
    private $validationProxy;

    /** @var IndexRateApiSdk */
    private $indexRateApiSdk;

    /**
     * MerchantPortalService constructor.
     * @param \PDO $pdo
     * @param Calculator $calculatorProxy
     * @param Validation $validationProxy
     * @param IndexRateApiSdk $indexRateApiSdk
     */
    function __construct(
        \PDO $pdo,
        Calculator $calculatorProxy,
        Validation $validationProxy,
        IndexRateApiSdk $indexRateApiSdk
    ) {
        $this->pdo = $pdo;
        $this->calculatorProxy = $calculatorProxy;
        $this->validationProxy = $validationProxy;
        $this->indexRateApiSdk = $indexRateApiSdk;
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        // We're looking for a drop in replacement of an MD5, wanting
        // to make sure we're better at being unique while not upsetting
        // possible earlier URL whitelists or the like.
        // Representing one byte in hex takes two characters and
        // MD5 produces 32 character strings, so we're going for
        // 16 random bytes
        $randomPseudoMd5 = bin2hex(random_bytes(16));

        return $randomPseudoMd5;
    }

    /**
     * @param Application $model
     * @return mixed
     * @throws ResourceNotFoundException
     */
    public function validateTenant(Application $model)
    {
        $statement = $this->pdo->prepare('SELECT
            `e`.`code`,
            `e`.`name`,
            `e`.`settings`,
            (
                SELECT GROUP_CONCAT(`c`.`country_id`) FROM `platform_environment_country` AS `c`
                WHERE `c`.`platform_environment_id` = `e`.`code`
            ) AS `countries`
        FROM `platform_environment` AS `e`
        WHERE `e`.`code` = :code AND `e`.`deleted_at` IS NULL');

        $statement->execute([
            ':code' => $model->getTenantId(),
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('tenant', 'id', $model->getTenantId());
        }

        $result = $statement->fetch();

        $result->countries = explode(",", $result->countries);
        $result->settings = json_decode($result->settings);

        return $result;
    }

    /**
     * @param Application $model
     * @return float|int
     */
    public function calculatePurchasePrice(Application $model): int
    {
        if (count($model->getProductData()) == 0) {
            return $model->getPurchasePrice();
        }

        $purchasePrice = 0;

        foreach ($model->getProductData() as $productData) {
            $quantity = $productData->quantity ?? 1;
            $purchasePrice += floatval($quantity) * intval($productData->price);
        }

        return $purchasePrice;
    }

    /**
     * @param Application $model
     * @return Application
     */
    public function validateProductData(Application $model): Application
    {
        if (count($model->getProductData()) == 0) {
            $model->setProductData([
                [
                    'sku' => "",
                    'name' => "Credit application",
                    'quantity' => 1,
                    'price' => $model->getPurchasePrice(),
                    'vat' => 0,
                    'unit' => "",
                    'image' => "",
                    'attributes' => null
                ]
            ]);
        }

        return $model;
    }

    /**
     * @param Application $model
     * @return object
     * @throws ApplicationInputInvalidException
     */
    public function validateMerchant(Application $model): object
    {
        $statement = $this->pdo->prepare('SELECT
            `m`.`id`,
            `m`.`active`,
            `m`.`branch_id`,
            `m`.`name`,
            `m`.`settings`
        FROM `merchant` AS `m`
        WHERE `m`.`id` = :id AND `m`.`platform_environment_id` = :tenant_id AND `m`.`deleted_at` IS NULL');

        $statement->execute([
            ':id' => $model->getMerchantId(),
            ':tenant_id' => $model->getTenantId()
        ]);

        if (!$statement->rowCount()) {
            throw new ApplicationInputInvalidException('merchant_id', 'not_found', $model->getMerchantId());
        }

        $result = $statement->fetch();
        $result->settings = json_decode($result->settings, 0);

        if (!$result->active) {
            throw new ApplicationInputInvalidException('merchant_id', 'not_active', $model->getMerchantId());
        }

        return $result;
    }

    /**
     * @param Application $model
     * @return object
     * @throws ApplicationInputInvalidException
     */
    public function validateChannel(Application $model): object
    {
        $start = microtime(true);

        $statement = $this->pdo->prepare('SELECT
            `c`.`id`,
            `c`.`type`,
            `c`.`name`
        FROM `merchant_channel` AS `c`
        WHERE `c`.`id` = :id AND `c`.`merchant_id` = :merchant_id AND `c`.`deleted_at` IS NULL');

        $statement->execute([
            ':id' => $model->getMerchantChannelId(),
            ':merchant_id' => $model->getMerchantId()
        ]);

        if (!$statement->rowCount()) {
            throw new ApplicationInputInvalidException('channel_id', 'not_found', $model->getMerchantChannelId());
        }

        $result = $statement->fetch();

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return $result;
    }

    /**
     * @param Application $model
     * @return object
     * @throws ApplicationInputInvalidException
     */
    public function validateFinanceOption(Application $model): object
    {
        $start = microtime(true);

        $statement = $this->pdo->prepare('SELECT
            `f`.`id`,
            `f`.`country_id` as `country_code`,
            `f`.`description`,
            `f`.`active`,
            `f`.`interest_rate_percentage`,
            `f`.`agreement_duration_months`,
            `f`.`deferral_period_months`,
            `f`.`order`,
            `f`.`minimum_amount`,
            `f`.`maximum_amount`,
            `f`.`minimum_deposit_percentage`,
            `f`.`maximum_deposit_percentage`,
            `f`.`margin_rate_percentage`,
            `f`.`merchant_minimum_deposit_percentage`,
            `f`.`merchant_maximum_deposit_percentage`,
            `f`.`minimum_repayment_amount`,
            `f`.`minimum_repayment_percentage`,
            `f`.`finance_settings`,
            `f`.`setup_fee_amount`,
            `f`.`instalment_fee_amount`,
            `f`.`index_rate_name`,
            `l`.`app_name`
        FROM `merchant_finance_option` AS `f`
        LEFT JOIN `merchant_finance_plan` AS `p` ON (`p`.`merchant_finance_option_id` = `f`.`id`)
        LEFT JOIN `lender` AS `l` ON (`l`.`id` = `p`.`lender_id`)
        WHERE `f`.`id` = :id AND `f`.`merchant_id` = :merchant_id AND `f`.`deleted_at` IS NULL');

        $statement->execute([
            ':id' => $model->getMerchantFinanceOptionId(),
            ':merchant_id' => $model->getMerchantId()
        ]);

        if (!$statement->rowCount()) {
            throw new ApplicationInputInvalidException('merchant_finance_option', 'not_found', $model->getMerchantFinanceOptionId());
        }

        $result = $statement->fetch();
        $result->finance_settings = json_decode($result->finance_settings, 0);

        if ($result->country_code != $model->getCountryCode()) {
            throw new ApplicationInputInvalidException('country_code', 'not_found', $model->getCountryCode());
        }

        if ($model->getDepositAmount() == 0 && $model->getDepositPercentage() > 0) {
            $depositAmount = $model->getPurchasePrice() * $model->getDepositPercentage();
            $model->setDepositAmount($depositAmount);
        } else {
            $depositPercentage = $model->getDepositAmount() / $model->getPurchasePrice();
            $model->setDepositPercentage($depositPercentage);
        }
        $creditAmount = $model->getPurchasePrice() - $model->getDepositAmount();

        if (!$result->active) {
            throw new UnauthorizedException();
        }

        if ($creditAmount < $result->minimum_amount) {
            throw new ApplicationInputInvalidException('credit_amount', 'too_low', $creditAmount . " lower than " . $result->minimum_amount);
        }

        if ($result->maximum_amount > 0 && $creditAmount > $result->maximum_amount) {
            throw new ApplicationInputInvalidException('credit_amount', 'too_high', $creditAmount . " higher than " . $result->maximum_amount);
        }

        if (round($model->getDepositPercentage(), 4) < round(($result->minimum_deposit_percentage - 0.005), 4)) {
            throw new ApplicationInputInvalidException('deposit_amount', 'too_low', round($model->getDepositPercentage(), 4) . " lower than " . round(($result->minimum_deposit_percentage - 0.005), 4));
        }

        if (round($model->getDepositPercentage(), 4) > round(($result->maximum_deposit_percentage + 0.005), 4)) {
            throw new ApplicationInputInvalidException('deposit_amount', 'too_high', round($model->getDepositPercentage(), 4) . " higher than " . round(($result->maximum_deposit_percentage + 0.005), 4));
        }

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return $result;
    }

    /**
     * @param Application $model
     * @param object $financeOption
     * @return array
     * @throws \Divido\IndexRateSdk\Exception
     */
    public function generateFinanceSettings(Application $model, object $financeOption)
    {
        $start = microtime(true);

        $financeSettings = [
            'amount' => $model->getPurchasePrice(),
            'deposit_amount' => $model->getDepositAmount(),
            'plan' => [
                'agreement_duration_months' => (int) $financeOption->agreement_duration_months,
                'calculation_family' => md5($financeOption->app_name),
                'country_code' => $financeOption->country_code,
                'credit_amount' => [
                    'minimum_amount' => (int) $financeOption->minimum_amount,
                    'maximum_amount' => (int) $financeOption->maximum_amount,
                ],
                'deferral_period_months' => (int) $financeOption->deferral_period_months,
                'deposit' => [
                    'minimum_percentage' => (float) $financeOption->minimum_deposit_percentage,
                    'maximum_percentage' => (float) $financeOption->maximum_deposit_percentage,
                ],
                'description' => $financeOption->description,
                'fees' => [
                    'instalment_fee_amount' => (int) $financeOption->instalment_fee_amount,
                    'setup_fee_amount' => (int) $financeOption->setup_fee_amount,
                ],
                'id' => $financeOption->id,
                'repayment' => [
                    'minimum_amount' => (int) $financeOption->minimum_repayment_amount,
                    'minimum_percentage' => (float) $financeOption->minimum_repayment_percentage,
                ],
                'margin_rate_percentage' => (float) $financeOption->margin_rate_percentage,
                'interest_rate_percentage' => (float) $financeOption->interest_rate_percentage,
                'lender_code' => null,
                'index_rate' => ['percentage' => 0, 'registered_at' => null],
            ],
        ];

        if (!empty($financeOption->index_rate_name)) {
            try {
                $indexRate = $this->indexRateApiSdk->get($financeOption->index_rate_name);
                $financeSettings['plan']['index_rate'] = [
                    'percentage' => $indexRate->getPercentage(),
                    'registered_at' => $indexRate->getRegisteredAt()->format('Y-m-d'),
                ];
            } catch (RateNotFoundException $exception) {
                $this->logger->warning('Index rate not found', ['rate' => $financeOption->index_rate_name]);
            }
        }

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return $financeSettings;
    }

    /**
     * @param Application $model
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFinanceTerms(Application $model)
    {
        $terms = (object) [];

        if ($model->getPurchasePrice() !== 0) {
            $terms = $this->calculatorProxy->getCalculations($model->getFinanceSettings());
        }

        return $terms;
    }

    /**
     * @param Application $model
     * @param $merchant
     * @param $channel
     * @return string
     */
    public function getDepositStatus(Application $model, $merchant, $channel)
    {
        $start = microtime(true);

        $depositStatus = $model->getDepositStatus();

        if ($channel->type == 'store') {
            if (!empty($merchant->settings->deposit->collect_manually_for_in_store) && $merchant->settings->deposit->collect_manually_for_in_store) {
                $depositStatus = 'UNPAID-COLLECT-MANUALLY';
            }
        } else if ($channel->type == 'webshop') {
            if (!empty($merchant->settings->deposit->collect_manually_for_online) && $merchant->settings->deposit->collect_manually_for_online) {
                $depositStatus = 'UNPAID-COLLECT-MANUALLY';
            }
        }

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return $depositStatus;
    }

    /**
     * @param Application $model
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function populateFormData(Application $model)
    {
        $start = microtime(true);

        $formData = $model->getFormData();

        if (empty($formData->gender) && !empty($formData->firstName)) {
            $gender = $this->validationProxy->suggestGender($formData->firstName, $model->getCountryCode());

            if ($gender) {
                $formData->gender = $gender;
            }
        }

        if (isset($formData->addresses) && is_array($formData->addresses) && count($formData->addresses) > 0) {
            foreach ($formData->addresses as $i => $address) {
                if (!empty($address->text)) {
                    $suggestion = $this->validationProxy->suggestAddress($address->text, $address->postcode, $model->getCountryCode());

                    if (!$suggestion) {
                        continue;
                    }

                    if (!empty($suggestion->postcode)) {
                        $address->postcode = $suggestion->postcode;
                    }
                    if (empty($address->flat)) {
                        $address->flat = $suggestion->flat;
                    }
                    if (empty($address->buildingNumber)) {
                        $address->buildingNumber = $suggestion->building_number;
                    }
                    if (empty($address->building_name)) {
                        $address->buildingName = $suggestion->building_name;
                    }
                    if (empty($address->street)) {
                        $address->street = $suggestion->street;
                    }
                    if (empty($address->town)) {
                        $address->town = $suggestion->town;
                    }
                    if (empty($address->monthsAtAddress) && !empty($suggestion->postcode)) {
                        $address->monthsAtAddress = 36;
                    }
                    if (empty($address->country) && !empty($suggestion->country_code)) {
                        $address->country = $suggestion->country_code;
                    }

                    $formData->addresses[$i] = $address;
                }
            }
        }

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return $formData;
    }

    /**
     * @param Application $model
     * @return bool
     * @throws ApplicationInputInvalidException
     */
    public function validateMerchantApiKey(Application $model)
    {
        if (!empty($model->getMerchantApiKeyId())) {
            $start = microtime(true);

            $statement = $this->pdo->prepare('SELECT
                `k`.`id`
            FROM `merchant_api_key` AS `k`
            WHERE `k`.`id` = :id AND `k`.`deleted_at` IS NULL');

            $statement->execute([
                ':id' => $model->getMerchantApiKeyId()
            ]);

            if (!$statement->rowCount()) {
                throw new ApplicationInputInvalidException('merchant_api_key', 'not_found', $model->getMerchantApiKeyId());
            }

            $result = $statement->fetch();

            $end = microtime(true);
            $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

            return $result;
        }

        return true;

    }

    /**
     * @param Application $model
     * @return bool
     * @throws ApplicationInputInvalidException
     */
    public function validateMerchantUser(Application $model)
    {
        if (!empty($model->getMerchantUserId())) {
            $start = microtime(true);

            $statement = $this->pdo->prepare('SELECT
                `u`.`id`,`u`.`name`
            FROM `merchant_user` AS `u`
            WHERE `u`.`id` = :id AND `u`.`deleted_at` IS NULL');

            $statement->execute([
                ':id' => $model->getMerchantUserId()
            ]);

            if (!$statement->rowCount()) {
                throw new ApplicationInputInvalidException('merchant_user_id', 'not_found', $model->getMerchantUserId());
            }

            $result = $statement->fetch();

            $end = microtime(true);
            $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

            return $result;
        }

        return true;

    }

    /**
     * @param Application $model
     * @param $environment
     * @return object
     * @throws ApplicationInputInvalidException
     */
    public function validateCountryCode(Application $model, $environment)
    {
        $start = microtime(true);

        if (!in_array($model->getCountryCode(), $environment->countries)) {
            throw new ApplicationInputInvalidException('country_code', 'not_found', $model->getCountryCode());
        }

        $statement = $this->pdo->prepare('SELECT
            `c`.`code`,
            `c`.`currency_id` as `currency_code`,
            `c`.`language_id` as `language_code`
        FROM `country` AS `c`
        WHERE `c`.`code` = :code');

        $statement->execute([
            ':code' => $model->getCountryCode(),
        ]);

        if (!$statement->rowCount()) {
            throw new ApplicationInputInvalidException('country_code', 'not_found', $model->getCountryCode());
        }

        $result = $statement->fetch();

        $end = microtime(true);
        $this->logMicrotime(($end - $start), __METHOD__, ['application_id' => $model->getId()]);

        return (object) [
            'code' => $result->code,
            'currency_code' => $result->currency_code,
            'languages' => [$result->language_code]
        ];

    }

    /**
     * @param Application $model
     * @param $country
     * @return bool
     * @throws ApplicationInputInvalidException
     */
    public function validateCurrencyCode(Application $model, $country)
    {
        if ($model->getCurrencyCode() != $country->currency_code) {
            throw new ApplicationInputInvalidException('currency_code', 'not_found', $model->getCurrencyCode());
        }

        return true;
    }

    /**
     * @param Application $model
     * @param $country
     * @return bool
     * @throws ApplicationInputInvalidException
     */
    public function validateLanguageCode(Application $model, $country)
    {
        if (!in_array($model->getLanguageCode(), $country->languages)) {
            throw new ApplicationInputInvalidException('language_code', 'not_found', $model->getLanguageCode());
        }

        return true;
    }

    private function logMicrotime($time, $method, $data)
    {
        $this->logger->info('microtime', [
            'class' => __CLASS__,
            'method' => $method,
            'time' => $time,
            'data' => $data,
        ]);
    }
}
