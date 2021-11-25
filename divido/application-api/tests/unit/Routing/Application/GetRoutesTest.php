<?php

namespace Divido\Test\Unit\Routing\Application;

use DateTime;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Routing\Application\GetRoutes;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Tenant\Tenant;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        $applicants = (object) [
            'value' => [
                (object) [
                    'value' => (object) [
                        'personal_details' => (object) [
                            'value' => (object) [
                                'first_name' => (object) [
                                    'value' => 'Ann'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return (new Application())
            ->setId($id)
            ->setToken('-token-')
            ->setTenantId('divido')
            ->setBranchId('-branch-uuid-')
            ->setApplicationSubmissionId('-submission-uuid-')
            ->setCountryCode('-country-code-')
            ->setCurrencyCode('-currency-code-')
            ->setLanguageCode('-language-code-')
            ->setMerchantId('-merchant-id-')
            ->setCustomerId('-customer-id-')
            ->setMerchantFinanceOptionId('-merchant-finance-id-')
            ->setMerchantChannelId('-merchant-channel-id-')
            ->setMerchantApiKeyId('-merchant-api-key-id-')
            ->setMerchantUserId('-merchant-user-id')
            ->setFinalised(1)
            ->setFinalisationRequired(0)
            ->setStatus('ACCEPTED')
            ->setPurchasePrice(100000)
            ->setDepositAmount(10000)
            ->setDepositPercentage(0)
            ->setDepositStatus('UNPAID')
            ->setLenderFee(0)
            ->setLenderFeeReportedDate(new DateTime())
            ->setFormData((object) ['firstName' => 'Ann'])
            ->setApplicants($applicants)
            ->setProductData([(object) ['sku' => 'sku', 'price' => 100000]])
            ->setMetadata((object) ['key' => 'value'])
            ->setCommission(100)
            ->setPartnerCommission(50)
            ->setMerchantReference('-merchant-reference-')
            ->setMerchantResponseUrl('-merchant-response-url-')
            ->setMerchantCheckoutUrl('-merchant-checkout-url-')
            ->setMerchantRedirectUrl('-merchant-redirect-url-')
            ->setApplicationFormUrl(
                (new Tenant())->setSettings(['urls' => ['application_form' => 'https://apply.divido.com']]),
                []
            )
            ->setFinanceSettings((object) ['new' => true])
            ->setTerms((object) ['amounts' => (object) ['credit_amount' => 100000]])
            ->setAvailableFinanceOptions(['-finance-id-'])
            ->setCancelledAmountTotal(0)
            ->setCancelledAmount(0)
            ->setActivatedAmountTotal(0)
            ->setActivatedAmount(0)
            ->setRefundedAmountTotal(0)
            ->setRefundedAmount(0)
            ->setSignerCollectionId('-signer-collection-id')
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_GetOne_WhenResourceNotFound()
    {
        $id = '-uuid-';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['id' => $id]);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Application'] = $mockService;

        $exception = null;

        try {
            $routes->getOne($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_GetOne_Success()
    {
        $id = '-uuid-';

        $request = $this->createRequest('get', ['id' => $id]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getOne($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;

        self::assertSame($mockModel->getId(), $data->id);
        self::assertSame($mockModel->getToken(), $data->token);
        self::assertSame($mockModel->getTenantId(), $data->tenant_id);
        self::assertSame($mockModel->getTenantId(), $data->platform_environment);
        self::assertSame($mockModel->getBranchId(), $data->branch_id);
        self::assertSame($mockModel->getApplicationSubmissionId(), $data->application_submission_id);
        self::assertSame($mockModel->getCountryCode(), $data->country_code);
        self::assertSame($mockModel->getCurrencyCode(), $data->currency_code);
        self::assertSame($mockModel->getLanguageCode(), $data->language_code);
        self::assertSame($mockModel->getMerchantId(), $data->merchant_id);
        self::assertSame($mockModel->getCustomerId(), $data->customer_id);
        self::assertSame($mockModel->getMerchantChannelId(), $data->merchant_channel_id);
        self::assertSame($mockModel->getMerchantApiKeyId(), $data->merchant_api_key_id);
        self::assertSame($mockModel->getMerchantUserId(), $data->merchant_user_id);
        self::assertSame($mockModel->getMerchantApiKeyId(), $data->merchant_api_key_id);
        self::assertSame($mockModel->isFinalised(), $data->finalised);
        self::assertSame($mockModel->isFinalisationRequired(), $data->finalisation_required);
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getPurchasePrice(), $data->purchase_price);
        self::assertSame($mockModel->getDepositAmount(), $data->deposit_amount);
        self::assertSame($mockModel->getDepositStatus(), $data->deposit_status);
        self::assertSame($mockModel->getLenderFee(), $data->lender_fee);
        self::assertSame($mockModel->getFormData()->firstName, $data->form_data->firstName);
        self::assertSame($mockModel->getApplicants()->value[0]->value->personal_details->value->first_name->value, $data->applicants->value[0]->value->personal_details->value->first_name->value);
        self::assertSame($mockModel->getProductData()[0]->sku, $data->product_data[0]->sku);
        self::assertSame($mockModel->getProductData()[0]->price, $data->product_data[0]->price);
        self::assertSame($mockModel->getMetadata()->key, $data->metadata->key);
        self::assertSame($mockModel->getCommission(), $data->commission);
        self::assertSame($mockModel->getPartnerCommission(), $data->partner_commission);
        self::assertSame($mockModel->getMerchantReference(), $data->merchant_reference);
        self::assertSame($mockModel->getMerchantResponseUrl(), $data->merchant_response_url);
        self::assertSame($mockModel->getMerchantCheckoutUrl(), $data->merchant_checkout_url);
        self::assertSame($mockModel->getMerchantRedirectUrl(), $data->merchant_redirect_url);
        self::assertSame($mockModel->getApplicationFormUrl(), $data->application_form_url);
        self::assertSame($mockModel->getFinanceSettings()->new, $data->finance_settings->new);
        self::assertSame($mockModel->getTerms()->amounts->credit_amount, $data->terms->amounts->credit_amount);
        self::assertSame($mockModel->getMerchantFinanceOptionId(), $data->merchant_finance_option_id);
        self::assertSame($mockModel->getAvailableFinanceOptions()[0], $data->available_finance_options[0]);
        self::assertObjectHasAttribute('pin_code', $data);
        self::assertSame($mockModel->getCancelledAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getCancelledAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getCancelableAmount(), $data->cancelable_amount);
        self::assertSame($mockModel->getActivatedAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getActivatedAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getActivatableAmount(), $data->activatable_amount);
        self::assertSame($mockModel->getRefundedAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getRefundedAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getRefundableAmount(), $data->refundable_amount);
        self::assertSame($mockModel->getSignerCollectionId(), $data->signer_collection_id);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }

    public function test_GetAll_WhenNoRecordsExists()
    {
        $applicationId = '-app-uuid-';
        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([]);

        $this->container['Service.Application'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertIsArray($data);
        self::assertCount(0, $data);
    }

    public function test_GetOneWithExtra_Success()
    {
        $id = '-uuid-';

        $request = $this->createRequest('get', ['id' => $id], ['extra' => true]);

        $mockModel = $this->getModel($id);

        $mockApplicationObjectBuilder = \Mockery::spy(ApplicationObjectBuilder::class);
        $mockApplicationObjectBuilder->shouldReceive('getObject')
            ->once()
            ->withArgs(function ($applicationModel) {
                self::assertInstanceOf(Application::class, $applicationModel);

                return true;
            })
            ->andReturnUsing(function () use ($id) {
                return (object) [
                    'id' => $id
                ];
            });

        $this->container['Helper.ApplicationObjectBuilder'] = $mockApplicationObjectBuilder;

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getOne($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;

        self::assertSame($id, $data->id);
    }

    public function test_GetAll_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-uuid-';

        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([$mockModel]);

        $this->container['Service.Application'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        self::assertIsArray($json->data);
        self::assertCount(1, $json->data);
        self::assertIsObject($json->data[0]);
        $data = $json->data[0];
        self::assertSame($mockModel->getId(), $data->id);
        self::assertSame($mockModel->getToken(), $data->token);
        self::assertSame($mockModel->getTenantId(), $data->tenant_id);
        self::assertSame($mockModel->getTenantId(), $data->platform_environment);
        self::assertSame($mockModel->getBranchId(), $data->branch_id);
        self::assertSame($mockModel->getApplicationSubmissionId(), $data->application_submission_id);
        self::assertSame($mockModel->getCountryCode(), $data->country_code);
        self::assertSame($mockModel->getCurrencyCode(), $data->currency_code);
        self::assertSame($mockModel->getLanguageCode(), $data->language_code);
        self::assertSame($mockModel->getMerchantId(), $data->merchant_id);
        self::assertSame($mockModel->getCustomerId(), $data->customer_id);
        self::assertSame($mockModel->getMerchantChannelId(), $data->merchant_channel_id);
        self::assertSame($mockModel->getMerchantApiKeyId(), $data->merchant_api_key_id);
        self::assertSame($mockModel->getMerchantUserId(), $data->merchant_user_id);
        self::assertSame($mockModel->getMerchantApiKeyId(), $data->merchant_api_key_id);
        self::assertSame($mockModel->isFinalised(), $data->finalised);
        self::assertSame($mockModel->isFinalisationRequired(), $data->finalisation_required);
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getPurchasePrice(), $data->purchase_price);
        self::assertSame($mockModel->getDepositAmount(), $data->deposit_amount);
        self::assertSame($mockModel->getDepositStatus(), $data->deposit_status);
        self::assertSame($mockModel->getLenderFee(), $data->lender_fee);
        self::assertSame($mockModel->getFormData()->firstName, $data->form_data->firstName);
        self::assertSame($mockModel->getApplicants()->value[0]->value->personal_details->value->first_name->value, $data->applicants->value[0]->value->personal_details->value->first_name->value);
        self::assertSame($mockModel->getProductData()[0]->sku, $data->product_data[0]->sku);
        self::assertSame($mockModel->getProductData()[0]->price, $data->product_data[0]->price);
        self::assertSame($mockModel->getMetadata()->key, $data->metadata->key);
        self::assertSame($mockModel->getCommission(), $data->commission);
        self::assertSame($mockModel->getPartnerCommission(), $data->partner_commission);
        self::assertSame($mockModel->getMerchantReference(), $data->merchant_reference);
        self::assertSame($mockModel->getMerchantResponseUrl(), $data->merchant_response_url);
        self::assertSame($mockModel->getMerchantCheckoutUrl(), $data->merchant_checkout_url);
        self::assertSame($mockModel->getMerchantRedirectUrl(), $data->merchant_redirect_url);
        self::assertSame($mockModel->getApplicationFormUrl(), $data->application_form_url);
        self::assertSame($mockModel->getFinanceSettings()->new, $data->finance_settings->new);
        self::assertSame($mockModel->getTerms()->amounts->credit_amount, $data->terms->amounts->credit_amount);
        self::assertSame($mockModel->getMerchantFinanceOptionId(), $data->merchant_finance_option_id);
        self::assertSame($mockModel->getAvailableFinanceOptions()[0], $data->available_finance_options[0]);
        self::assertObjectHasAttribute('pin_code', $data);
        self::assertSame($mockModel->getCancelledAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getCancelledAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getCancelableAmount(), $data->cancelable_amount);
        self::assertSame($mockModel->getActivatedAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getActivatedAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getActivatableAmount(), $data->activatable_amount);
        self::assertSame($mockModel->getRefundedAmount(), $data->cancelled_amount);
        self::assertSame($mockModel->getRefundedAmountTotal(), $data->cancelled_amount_total);
        self::assertSame($mockModel->getRefundableAmount(), $data->refundable_amount);
        self::assertSame($mockModel->getSignerCollectionId(), $data->signer_collection_id);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }
}
