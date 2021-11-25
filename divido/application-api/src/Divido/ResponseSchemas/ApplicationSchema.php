<?php

namespace Divido\ResponseSchemas;

use Divido\Services\Application\Application;

/**
 * Class ApplicationSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class ApplicationSchema
{
    /**
     * @param Application $resource
     * @return array|null
     */
    public function getData(Application $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'token' => $resource->getToken(),
            'platform_environment' => $resource->getTenantId(),
            'tenant_id' => $resource->getTenantId(),
            'branch_id' => $resource->getBranchId(),
            'application_submission_id' => $resource->getApplicationSubmissionId(),
            'country_code' => $resource->getCountryCode(),
            'currency_code' => $resource->getCurrencyCode(),
            'language_code' => $resource->getLanguageCode(),
            'merchant_id' => $resource->getMerchantId(),
            'customer_id' => $resource->getCustomerId(),
            'merchant_channel_id' => $resource->getMerchantChannelId(),
            'merchant_api_key_id' => $resource->getMerchantApiKeyId(),
            'merchant_user_id' => $resource->getMerchantUserId(),
            'finalised' => $resource->isFinalised(),
            'finalisation_required' => $resource->isFinalisationRequired(),
            'status' => $resource->getStatus(),
            'purchase_price' => $resource->getPurchasePrice(),
            'deposit_amount' => $resource->getDepositAmount(),
            'deposit_status' => $resource->getDepositStatus(),
            'lender_fee' => $resource->getLenderFee(),
            'lender_fee_reported_date' => ($resource->getLenderFeeReportedDate()) ? $resource->getLenderFeeReportedDate()->format("c") : null,
            'form_data' => $resource->getFormData(),
            'applicants' => $resource->getApplicants(),
            'product_data' => $resource->getProductData(),
            'metadata' => $resource->getMetadata(),
            'commission' => $resource->getCommission(),
            'partner_commission' => $resource->getPartnerCommission(),
            'merchant_reference' => $resource->getMerchantReference(),
            'merchant_response_url' => $resource->getMerchantResponseUrl(),
            'merchant_checkout_url' => $resource->getMerchantCheckoutUrl(),
            'merchant_redirect_url' => $resource->getMerchantRedirectUrl(),
            'application_form_url' => $resource->getApplicationFormUrl(),
            'finance_settings' => $resource->getFinanceSettings(),
            'terms' => $resource->getTerms(),
            'merchant_finance_option_id' => $resource->getMerchantFinanceOptionId(),
            'available_finance_options' => $resource->getAvailableFinanceOptions(),
            'pin_code' => $resource->getPinCode(),
            'cancelled_amount' => $resource->getCancelledAmount(),
            'cancelled_amount_total' => $resource->getCancelledAmountTotal(),
            'cancelable_amount' => $resource->getCancelableAmount(),
            'activated_amount' => $resource->getActivatedAmount(),
            'activated_amount_total' => $resource->getActivatedAmountTotal(),
            'activatable_amount' => $resource->getActivatableAmount(),
            'refunded_amount' => $resource->getRefundedAmount(),
            'refunded_amount_total' => $resource->getRefundedAmountTotal(),
            'refundable_amount' => $resource->getRefundableAmount(),
            'signer_collection_id' => $resource->getSignerCollectionId(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
