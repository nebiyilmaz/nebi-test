<?php

namespace Divido\Services\Application;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Proxies\ApplicationSubmissionApi as ApplicationSubmissionApiProxy;
use Divido\MerchantApiSdk\Models\FinanceOption\FinanceOption;
use Divido\MerchantApiSdk\Client as MerchantApiSdk;
use Divido\MerchantApi\MerchantApiClientException;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionDatabaseInterface;
use Divido\WaterfallApiSdk\Client as WaterfallApiSdk;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * Class ApplicationSubmissionService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationSubmissionService
{
    /** @var PDO $pdo */
    private $pdo;

    /** @var SubmissionDatabaseInterface $submissionDatabaeInterface */
    private $submissionDatabaseInterface;

    /** @var ApplicationSubmissionApiProxy $applicationSubmissionApiProxy */
    private $applicationSubmissionApiProxy;

    /** @var MerchantApiSdk $merchantApiSdk */
    private $merchantApiSdk;

    /** @var WaterfallApiSdk $waterfallApiSdk */
    private $waterfallApiSdk;

    /**
     * MerchantPortalService constructor.
     * @param PDO $pdo
     * @param SubmissionDatabaseInterface $submissionDatabaseInterface
     * @param ApplicationSubmissionApiProxy $applicationSubmissionApiProxy
     * @param MerchantApiSdk $merchantApiSdk
     * @param WaterfallApiSdk $waterfallApiSdk
     */
    function __construct(
        PDO $pdo,
        SubmissionDatabaseInterface $submissionDatabaseInterface,
        ApplicationSubmissionApiProxy $applicationSubmissionApiProxy,
        MerchantApiSdk $merchantApiSdk,
        WaterfallApiSdk $waterfallApiSdk
    ) {
        $this->pdo = $pdo;
        $this->submissionDatabaseInterface = $submissionDatabaseInterface;
        $this->applicationSubmissionApiProxy = $applicationSubmissionApiProxy;
        $this->merchantApiSdk = $merchantApiSdk;
        $this->waterfallApiSdk = $waterfallApiSdk;
    }

    /**
     * @param Application $application
     * @return mixed
     * @throws MerchantApiClientException
     * @throws ResourceNotFoundException
     */
    public function createSubmissions(Application $application)
    {
        $financeOption = $this->merchantApiSdk->getOneFinanceOption($application->getMerchantFinanceOptionId());
        $submissions = $this->getSubmissionsFromWaterfall($financeOption);

        $newSubmissions = [];

        foreach ($submissions as $data) {
            $id = Uuid::uuid4()->toString();

            $newSubmission = (new Submission())
                ->setId($id)
                ->setApplicationId($application->getId())
                ->setOrder($data->order)
                ->setDeclineReferred(($data->decline_referred) ? 1 : 0)
                ->setLenderId($data->lender_id)
                ->setApplicationAlternativeOfferId($data->application_alternative_offer_id)
                ->setMerchantFinancePlanId($data->merchant_finance_plan_id)
                ->setStatus($data->status)
                ->setLenderReference($data->lender_reference)
                ->setLenderLoanReference($data->lender_loan_reference)
                ->setLenderStatus($data->lender_status)
                ->setLenderData($data->lender_data)
                ->setLenderCode($data->lender_code ?? "");

            $this->submissionDatabaseInterface->createNewSubmissionFromModel($newSubmission);
            $newSubmissions[] = $this->submissionDatabaseInterface->getSubmissionFromModel($newSubmission, false);
        }

        if (count($newSubmissions) > 0) {
            $application->setApplicationSubmissionId($newSubmissions[0]->getId());
        }

        return $application;

    }

    /**
     * @param FinanceOption $financeOption
     * @return mixed
     */
    public function getSubmissionsFromWaterfall(FinanceOption $financeOption)
    {
        $financePlans = [];
        foreach ($financeOption->getFinancePlans() as $financePlan) {
            $financePlans[] = [
                'id' => $financePlan->getId(),
                'finance_option_id' => $financePlan->getFinanceOptionId(),
                'decision_rule_template_id' => $financePlan->getDecisionRuleTemplateId(),
                'lender_id' => $financePlan->getLenderId(),
                'lender_code' => $financePlan->getLenderCode(),
                'commission_percentage' => $financePlan->getCommissionPercentage(),
                'partner_commission_percentage' => $financePlan->getPartnerCommissionPercentage(),
                'lender_fee_percentage' => $financePlan->getLenderFeePercentage(),
                'lender_fee_minimum_amount' => $financePlan->getLenderFeeMinimumAmount(),
                'created_at' => $financePlan->getCreatedAt()->format("c"),
                'updated_at' => $financePlan->getUpdatedAt()->format("c"),
            ];
        }

        $financeOption = [
            'id' => $financeOption->getId(),
            'merchant_id' => $financeOption->getMerchantId(),
            'type' => $financeOption->getType(),
            'country_code' => $financeOption->getCountryCode(),
            'order' => $financeOption->getOrder(),
            'active' => $financeOption->isActive(),
            'description' => $financeOption->getDescription(),
            'index_rate_name' => $financeOption->getIndexRateName(),
            'interest_rate_percentage' => $financeOption->getInterestRatePercentage(),
            'margin_rate_percentage' => $financeOption->getMarginRatePercentage(),
            'agreement_duration_months' => $financeOption->getAgreementDurationMonths(),
            'deferral_period_months' => $financeOption->getDeferralPeriodMonths(),
            'credit_amount' => $financeOption->getCreditAmount(),
            'repayments' => $financeOption->getRepayments(),
            'deposit' => $financeOption->getDeposit(),
            'fees' => $financeOption->getFees(),
            'waterfall_groups' => $financeOption->getWaterfallGroups(),
            'finance_plans' => $financePlans,
            'created_at' => $financeOption->getCreatedAt()->format("c"),
            'updated_at' => $financeOption->getUpdatedAt()->format("c"),
            'metadata' => $financeOption->getMetadata(),
        ];

        $response = $this->waterfallApiSdk->waterfall($financeOption);

        return $response->data;
    }

    /**
     * @param Application $application
     * @return bool
     */
    public function submitApplicationToApplicationSubmissionApi(Application $application)
    {
        $this->applicationSubmissionApiProxy->submitEvent($application->getId());

        return true;
    }
}
