<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Application;

use Divido\MerchantApiSdk\Client as MerchantApiSdk;
use Divido\MerchantApiSdk\Models\FinanceOption\FinanceOption;
use Divido\MerchantApiSdk\Models\FinancePlan\FinancePlan;
use Divido\Proxies\ApplicationSubmissionApi as ApplicationSubmissionApiProxy;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationSubmissionService;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionDatabaseInterface;
use Divido\WaterfallApiSdk\Client as WaterfallApiSdk;
use PDO;
use PHPUnit\Framework\TestCase;

class ApplicationSubmissionTest extends TestCase
{
    private const DEFAULT_URL = 'https://application-api.api.dev.divido.net/#/';

    private const V1_BASE_URL = 'https://apply.divido.com/#/';

    private const V2_BASE_URL = 'https://apply-v2.divido.com/#/';

    private const TOKEN = '-token-';

    public function test_CreateSubmission_AndReturnsApplication()
    {
        $pdo = self::createMock(PDO::class);
        $submissionDatabaseInterface = self::createMock(SubmissionDatabaseInterface::class);
        $applicationSubmissionApiProxy = self::createMock(ApplicationSubmissionApiProxy::class);
        $merchantApiSdk = self::createMock(MerchantApiSdk::class);
        $waterfallApiSdk = self::createMock(WaterfallApiSdk::class);
        $applicationSubmissionService = new ApplicationSubmissionService(
            $pdo,
            $submissionDatabaseInterface,
            $applicationSubmissionApiProxy,
            $merchantApiSdk,
            $waterfallApiSdk
        );


        $financePlan = (new FinancePlan())
            ->withId('-finance-plan-id-')
            ->withFinanceOptionId('-finance-option-id-')
            ->withDecisionRuleTemplateId('-decision-rule-template-id-')
            ->withLenderId('-lender-id-')
            ->withLenderCode('-lender-code-')
            ->withCommissionPercentage(0)
            ->withPartnerCommissionPercentage(0)
            ->withLenderFeePercentage(0)
            ->withLenderFeeMinimumAmount(0)
            ->withCreatedAt(new \DateTime())
            ->withUpdatedAt(new \DateTime());
        $financeOption = (new FinanceOption())
            ->withId('-finance-option-id-')
            ->withMerchantId('-merchant-id-')
            ->withType('store')
            ->withCountryCode('GB')
            ->withOrder(12345)
            ->withActive(true)
            ->withDescription('_description_')
            ->withIndexRateName('_index_rate_name_')
            ->withInterestRatePercentage(0)
            ->withMarginRatePercentage(0)
            ->withAgreementDurationMonths(12)
            ->withDeferralPeriodMonths(12)
            ->withCreditAmount([
                'minimum_amount' => 1000,
                'maximum_amount' => null,
            ])
            ->withRepayments([])
            ->withDeposit([
                'minimum_percentage' => 0.10,
                'maximum_percentage' => 0.10,
            ])
            ->withFees([
                'setup_fee_amount' => 1000,
                'instalment_fee_amount' => 1000,
            ])
            ->withWaterfallGroups([])
            ->withFinancePlans([$financePlan])
            ->withMetadata((object) [])
            ->withCreatedAt(new \DateTime())
            ->withUpdatedAt(new \DateTime());
        $merchantApiSdk->method('getOneFinanceOption')->willReturn($financeOption);


        $waterfallApiResponse = (object) [
            'data' => [
                (object) [
                    'order' => 1,
                    'decline_referred' => true,
                    'lender_id' => '',
                    'application_alternative_offer_id' => '',
                    'merchant_finance_plan_id' => '',
                    'status' => '',
                    'lender_reference' => '',
                    'lender_loan_reference' => '',
                    'lender_status' => '',
                    'lender_data' => (object) [],
                    'lender_code' => '',
                ],
            ],
        ];
        $waterfallApiSdk->method('waterfall')->willReturn($waterfallApiResponse);


        $submission = (new Submission())
            ->setId('-submission-id-');
        $submissionDatabaseInterface->method('getSubmissionFromModel')->willReturn($submission);


        $application = (new Application())
            ->setId('-application-id-')
            ->setMerchantFinanceOptionId('-merchant-finance-option-id-');
        $application = $applicationSubmissionService->createSubmissions($application);

        self::assertEquals('-submission-id-', $application->getApplicationSubmissionId());
    }
}
