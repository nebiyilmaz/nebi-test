<?php

namespace Divido\Services\Application;

use DateTime;
use Divido\Services\Tenant\Tenant;

/**
 * Class Application
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Application
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var string $tenantId
     */
    private $tenantId;

    /**
     * @var string $branchId
     */
    private $branchId;

    /**
     * @var string $applicationSubmissionId
     */
    private $applicationSubmissionId;

    /**
     * @var string $countryCode
     */
    private $countryCode;

    /**
     * @var string $currencyCode
     */
    private $currencyCode;

    /**
     * @var string $languageCode
     */
    private $languageCode;

    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $customerId
     */
    private $customerId;

    /**
     * @var string $merchantFinanceOptionId
     */
    private $merchantFinanceOptionId;

    /**
     * @var string $merchantChannelId
     */
    private $merchantChannelId;

    /**
     * @var string $merchantApiKeyId
     */
    private $merchantApiKeyId;

    /**
     * @var string $merchantUserId
     */
    private $merchantUserId;

    /**
     * @var boolean $finalised
     */
    private $finalised;

    /**
     * @var boolean $finalisationRequired
     */
    private $finalisationRequired;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var integer $purchasePrice
     */
    private $purchasePrice;

    /**
     * @var float $depositPercentage
     */
    private $depositPercentage;

    /**
     * @var integer $depositAmount
     */
    private $depositAmount;

    /**
     * @var string $depositStatus
     */
    private $depositStatus;

    /**
     * @var integer $lenderFee
     */
    private $lenderFee;

    /**
     * @var DateTime $lenderFeeReportedDate
     */
    private $lenderFeeReportedDate;

    /**
     * @var object $formData
     */
    private $formData;

    /**
     * @var object $applicants
     */
    private $applicants;

    /**
     * @var array $productData
     */
    private $productData;

    /**
     * @var object $metadata
     */
    private $metadata;

    /**
     * @var integer $commission
     */
    private $commission;

    /**
     * @var integer $partnerCommission
     */
    private $partnerCommission;

    /**
     * @var string $merchantReference
     */
    private $merchantReference;

    /**
     * @var string $merchantResponseUrl
     */
    private $merchantResponseUrl;

    /**
     * @var string $merchantCheckoutUrl
     */
    private $merchantCheckoutUrl;

    /**
     * @var string $merchantRedirectUrl
     */
    private $merchantRedirectUrl;

    /**
     * @var string $applicationFormUrl
     */
    private $applicationFormUrl;

    /**
     * @var object $financeSettings
     */
    private $financeSettings;

    /**
     * @var object $terms
     */
    private $terms;

    /**
     * @var array $availableFinanceOptions
     */
    private $availableFinanceOptions;

    /** @var int $cancelledAmountTotal */
    private $cancelledAmountTotal;

    /** @var int $cancelledAmount */
    private $cancelledAmount;

    /** @var int $activatedAmountTotal */
    private $activatedAmountTotal;

    /** @var int $activatedAmount */
    private $activatedAmount;

    /** @var int $refundedAmountTotal */
    private $refundedAmountTotal;

    /** @var int $refundedAmount */
    private $refundedAmount;

    /**
     * @var string $signerCollectionId
     */
    private $signerCollectionId;

    /**
     * @var DateTime $createdAt
     */
    private $createdAt;

    /**
     * @var DateTime $updatedAt
     */
    private $updatedAt;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Application
     */
    public function setId(string $id): Application
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnlyTokenSet()
    {
        return (empty($this->id) && !empty($this->token));
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Application
     */
    public function setToken(?string $token): Application
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * @param string $tenantId
     * @return Application
     */
    public function setTenantId(string $tenantId): Application
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranchId(): ?string
    {
        return $this->branchId;
    }

    /**
     * @param string $branchId
     * @return Application
     */
    public function setBranchId(?string $branchId): Application
    {
        $this->branchId = $branchId;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationSubmissionId(): ?string
    {
        return $this->applicationSubmissionId;
    }

    /**
     * @param string $applicationSubmissionId
     * @return Application
     */
    public function setApplicationSubmissionId(?string $applicationSubmissionId): Application
    {
        $this->applicationSubmissionId = $applicationSubmissionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return Application
     */
    public function setCountryCode(string $countryCode): Application
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     * @return Application
     */
    public function setCurrencyCode(?string $currencyCode): Application
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return Application
     */
    public function setLanguageCode(?string $languageCode): Application
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return Application
     */
    public function setMerchantId(string $merchantId): Application
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     * @return Application
     */
    public function setCustomerId(?string $customerId): Application
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantFinanceOptionId(): ?string
    {
        return $this->merchantFinanceOptionId;
    }

    /**
     * @param string $merchantFinanceOptionId
     * @return Application
     */
    public function setMerchantFinanceOptionId(?string $merchantFinanceOptionId): Application
    {
        $this->merchantFinanceOptionId = $merchantFinanceOptionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantChannelId(): string
    {
        return $this->merchantChannelId;
    }

    /**
     * @param string $merchantChannelId
     * @return Application
     */
    public function setMerchantChannelId(string $merchantChannelId): Application
    {
        $this->merchantChannelId = $merchantChannelId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantApiKeyId(): ?string
    {
        return $this->merchantApiKeyId;
    }

    /**
     * @param string $merchantApiKeyId
     * @return Application
     */
    public function setMerchantApiKeyId(?string $merchantApiKeyId): Application
    {
        $this->merchantApiKeyId = $merchantApiKeyId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantUserId(): ?string
    {
        return $this->merchantUserId;
    }

    /**
     * @param string $merchantUserId
     * @return Application
     */
    public function setMerchantUserId(?string $merchantUserId): Application
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinalised(): bool
    {
        return ($this->finalised) ? true : false;
    }

    /**
     * @param bool $finalised
     * @return Application
     */
    public function setFinalised(bool $finalised): Application
    {
        $this->finalised = $finalised;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinalisationRequired(): bool
    {
        return ($this->finalisationRequired) ? true : false;
    }

    /**
     * @param bool $finalisationRequired
     * @return Application
     */
    public function setFinalisationRequired(bool $finalisationRequired): Application
    {
        $this->finalisationRequired = $finalisationRequired;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Application
     */
    public function setStatus(string $status): Application
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getPurchasePrice(): int
    {
        return $this->purchasePrice;
    }

    /**
     * @param int $purchasePrice
     * @return Application
     */
    public function setPurchasePrice(int $purchasePrice): Application
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getDepositPercentage(): float
    {
        return round($this->depositPercentage, 4);
    }

    /**
     * @param float $depositPercentage
     * @return Application
     */
    public function setDepositPercentage(float $depositPercentage): Application
    {
        $this->depositPercentage = round($depositPercentage, 4);

        return $this;
    }

    /**
     * @return int
     */
    public function getDepositAmount(): int
    {
        return $this->depositAmount;
    }

    /**
     * @param int $depositAmount
     * @return Application
     */
    public function setDepositAmount(int $depositAmount): Application
    {
        $this->depositAmount = $depositAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDepositStatus(): string
    {
        return $this->depositStatus;
    }

    /**
     * @param string $depositStatus
     * @return Application
     */
    public function setDepositStatus(string $depositStatus): Application
    {
        $this->depositStatus = $depositStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getLenderFee(): int
    {
        return $this->lenderFee;
    }

    /**
     * @param int $lenderFee
     * @return Application
     */
    public function setLenderFee(int $lenderFee): Application
    {
        $this->lenderFee = $lenderFee;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLenderFeeReportedDate(): ?DateTime
    {
        return $this->lenderFeeReportedDate;
    }

    /**
     * @param DateTime $lenderFeeReportedDate
     * @return Application
     */
    public function setLenderFeeReportedDate(?DateTime $lenderFeeReportedDate): Application
    {
        $this->lenderFeeReportedDate = $lenderFeeReportedDate;

        return $this;
    }

    /**
     * @return object
     */
    public function getFormData(): object
    {
        return $this->formData;
    }

    /**
     * @param object $formData
     * @return Application
     */
    public function setFormData(object $formData): Application
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * @return object
     */
    public function getApplicants(): object
    {
        return $this->applicants;
    }

    /**
     * @param object $applicants
     * @return Application
     */
    public function setApplicants(object $applicants): Application
    {
        $this->applicants = $applicants;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductData(): array
    {
        return $this->productData;
    }

    /**
     * @param array $productData
     * @return Application
     */
    public function setProductData(array $productData): Application
    {
        $this->productData = $productData;

        return $this;
    }

    /**
     * @return object
     */
    public function getMetadata(): object
    {
        return $this->metadata;
    }

    /**
     * @param object $metadata
     * @return Application
     */
    public function setMetadata(object $metadata): Application
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return int
     */
    public function getCommission(): int
    {
        return $this->commission;
    }

    /**
     * @param int $commission
     * @return Application
     */
    public function setCommission(int $commission): Application
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * @return int
     */
    public function getPartnerCommission(): int
    {
        return $this->partnerCommission;
    }

    /**
     * @param int $partnerCommission
     * @return Application
     */
    public function setPartnerCommission(int $partnerCommission): Application
    {
        $this->partnerCommission = $partnerCommission;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @param string $merchantReference
     * @return Application
     */
    public function setMerchantReference(?string $merchantReference): Application
    {
        $this->merchantReference = $merchantReference ?? "";

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantResponseUrl(): ?string
    {
        return $this->merchantResponseUrl;
    }

    /**
     * @param string $merchantResponseUrl
     * @return Application
     */
    public function setMerchantResponseUrl(?string $merchantResponseUrl): Application
    {
        $this->merchantResponseUrl = $merchantResponseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantCheckoutUrl(): ?string
    {
        return $this->merchantCheckoutUrl;
    }

    /**
     * @param string $merchantCheckoutUrl
     * @return Application
     */
    public function setMerchantCheckoutUrl(?string $merchantCheckoutUrl): Application
    {
        $this->merchantCheckoutUrl = $merchantCheckoutUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantRedirectUrl(): ?string
    {
        return $this->merchantRedirectUrl;
    }

    /**
     * @param string $merchantRedirectUrl
     * @return Application
     */
    public function setMerchantRedirectUrl(?string $merchantRedirectUrl): Application
    {
        $this->merchantRedirectUrl = $merchantRedirectUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationFormUrl(): string
    {
        return $this->applicationFormUrl;
    }

    /**
     * @param Tenant $tenant
     * @param array $lenderSettings
     * @return Application
     */
    public function setApplicationFormUrl(Tenant $tenant, array $lenderSettings): Application
    {
        $settings = $tenant->getSettings();

        $url = (!empty($settings['urls']['application_form']))
            ? $settings['urls']['application_form']
            : "https://application-api.api.dev.divido.net/#/";

        if (!empty($settings['urls']['application_form_v2']) && ($lenderSettings['supports_v2'] ?? false)) {
            $url = $settings['urls']['application_form_v2'];
        }

        $this->applicationFormUrl = $url . $this->token;

        return $this;
    }

    /**
     * @return object
     */
    public function getFinanceSettings(): object
    {
        return $this->financeSettings;
    }

    /**
     * @param object $financeSettings
     * @return Application
     */
    public function setFinanceSettings(object $financeSettings): Application
    {
        $this->financeSettings = $financeSettings;

        return $this;
    }

    /**
     * @return object
     */
    public function getTerms(): object
    {
        return $this->terms;
    }

    /**
     * @param object $terms
     * @return Application
     */
    public function setTerms(object $terms): Application
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableFinanceOptions(): array
    {
        return $this->availableFinanceOptions;
    }

    /**
     * @param array $availableFinanceOptions
     * @return Application
     */
    public function setAvailableFinanceOptions(array $availableFinanceOptions): Application
    {
        $this->availableFinanceOptions = $availableFinanceOptions;

        return $this;
    }

    /**
     * @return int
     */
    public function getCancelledAmountTotal(): int
    {
        return $this->cancelledAmountTotal;
    }

    /**
     * @param int $cancelledAmountTotal
     * @return Application
     */
    public function setCancelledAmountTotal(int $cancelledAmountTotal): Application
    {
        $this->cancelledAmountTotal = $cancelledAmountTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getCancelledAmount(): int
    {
        return $this->cancelledAmount;
    }

    /**
     * @param int $cancelledAmount
     * @return Application
     */
    public function setCancelledAmount(int $cancelledAmount): Application
    {
        $this->cancelledAmount = $cancelledAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getActivatedAmountTotal(): int
    {
        return $this->activatedAmountTotal;
    }

    /**
     * @param int $activatedAmountTotal
     * @return Application
     */
    public function setActivatedAmountTotal(int $activatedAmountTotal): Application
    {
        $this->activatedAmountTotal = $activatedAmountTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getActivatedAmount(): int
    {
        return $this->activatedAmount;
    }

    /**
     * @param int $activatedAmount
     * @return Application
     */
    public function setActivatedAmount(int $activatedAmount): Application
    {
        $this->activatedAmount = $activatedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefundedAmountTotal(): int
    {
        return $this->refundedAmountTotal;
    }

    /**
     * @param int $refundedAmountTotal
     * @return Application
     */
    public function setRefundedAmountTotal(int $refundedAmountTotal): Application
    {
        $this->refundedAmountTotal = $refundedAmountTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefundedAmount(): int
    {
        return $this->refundedAmount;
    }

    /**
     * @param int $refundedAmount
     * @return Application
     */
    public function setRefundedAmount(int $refundedAmount): Application
    {
        $this->refundedAmount = $refundedAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getSignerCollectionId(): ?string
    {
        return $this->signerCollectionId;
    }

    /**
     * @param string $signerCollectionId
     * @return Application
     */
    public function setSignerCollectionId(?string $signerCollectionId): Application
    {
        $this->signerCollectionId = $signerCollectionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCancelableAmount()
    {
        return $this->getCreditAmount() - $this->getActivatedAmount() - $this->getCancelledAmount();
    }

    /**
     * @return int
     */
    public function getActivatableAmount()
    {
        return $this->getCreditAmount() - $this->getActivatedAmount() - $this->getCancelledAmount();
    }

    /**
     * @return int
     */
    public function getRefundableAmount()
    {
        return $this->getActivatedAmount() - $this->getRefundedAmount();
    }

    /**
     * @return int
     */
    public function getCreditAmount()
    {
        return $this->getPurchasePrice() - $this->getDepositAmount();
    }

    /**
     * NOTE:
     *
     * This code is copied to platform and is used when sending out status update emails, if we change anything here
     * we need to change the corresponding code in platform.
     *
     * @return string
     */
    public function getPinCode()
    {
        preg_match_all('/\d/', $this->id, $array);

        $temp = 0;

        for ($i = 0; $i < count($array[0]); $i++) {
            $num = (int) $array[0][$i];
            $temp += ($i % 3) ? $num * ($i + $i) : $i * ($num + $temp);
        }

        return (string) str_pad((int) substr($temp * $temp, 0, 4), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Application
     */
    public function setCreatedAt(DateTime $createdAt): Application
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     * @return Application
     */
    public function setUpdatedAt(DateTime $updatedAt): Application
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
