<?php

namespace Divido\Test\Unit\Routing\Application;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Application\PostRoutes;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Tenant\Tenant;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PostRoutesTest extends RouteTestCase
{
    public function test_Create_WhenNoData()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', [], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->create($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_Create_Success()
    {
        $id = '-uuid-';
        $token = '-token-';
        $tenantId = '-tenant-';

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

        $newData = [
            'country_code' => '-country_code-',
            'currency_code' => '-currency_code-',
            'language_code' => '-language_code-',
            'merchant_id' => '-merchant_id-',
            'merchant_channel_id' => '-merchant_channel_id-',
            'merchant_finance_option_id' => '-merchant_finance_option_id-',
            'merchant_api_key_id' => '-merchant_api_key_id-',
            'merchant_user_id' => '-merchant_user_id-',
            'finalisation_required' => false,
            'purchase_price' => 100000,
            'deposit_percentage' => 0,
            'deposit_amount' => 10000,
            'deposit_status' => 'UNPAID',
            'form_data' => (object) ['firstName' => 'Ann'],
            'applicants' => $applicants,
            'product_data' => [(object) ['sku' => 'sku', 'price' => 100000]],
            'metadata' => (object) ['key' => 'value'],
            'merchant_reference' => '-merchant_reference-',
            'merchant_response_url' => '-merchant_response_url-',
            'merchant_checkout_url' => '-merchant_checkout_url-',
            'merchant_redirect_url' => '-merchant_redirect_url-',
            'available_finance_options' => ['-available_finance_options-'],
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(ApplicationService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id, $token, $tenantId) {
                $model->setId($id)
                    ->setStatus('PROPOSAL')
                    ->setTenantId($tenantId)
                    ->setFinalised(0)
                    ->setToken($token)
                    ->setLenderFee(0)
                    ->setCommission(100)
                    ->setPartnerCommission(50)
                    ->setApplicationFormUrl(
                        (new Tenant())->setSettings(['urls'=>['application_form'=>'https://apply.divido.com']]),
                        [],
                    )
                    ->setFinanceSettings((object) [])
                    ->setTerms((object) [])
                    ->setCancelledAmount(0)
                    ->setCancelledAmountTotal(0)
                    ->setRefundedAmount(0)
                    ->setRefundedAmountTotal(0)
                    ->setActivatedAmount(0)
                    ->setActivatedAmountTotal(0)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.Application'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->create($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($id, $data->id);
        self::assertSame($token, $data->token);
        self::assertObjectHasAttribute('branch_id', $data);
        self::assertSame($tenantId, $data->tenant_id);
        self::assertSame($tenantId, $data->platform_environment);
        self::assertSame($newData['country_code'], $data->country_code);
        self::assertSame($newData['currency_code'], $data->currency_code);
        self::assertSame($newData['language_code'], $data->language_code);
        self::assertSame($newData['merchant_id'], $data->merchant_id);
        self::assertObjectHasAttribute('customer_id', $data);
        self::assertSame($newData['merchant_channel_id'], $data->merchant_channel_id);
        self::assertSame($newData['merchant_api_key_id'], $data->merchant_api_key_id);
        self::assertSame($newData['merchant_user_id'], $data->merchant_user_id);
        self::assertSame($newData['merchant_api_key_id'], $data->merchant_api_key_id);
        self::assertSame(false, $data->finalised);
        self::assertSame($newData['finalisation_required'], $data->finalisation_required);
        self::assertSame('PROPOSAL', $data->status);
        self::assertSame($newData['purchase_price'], $data->purchase_price);
        self::assertSame($newData['deposit_amount'], $data->deposit_amount);
        self::assertSame($newData['deposit_status'], $data->deposit_status);
        self::assertSame(0, $data->lender_fee);
        self::assertSame($newData['applicants']->value[0]->value->personal_details->value->first_name->value, $data->applicants->value[0]->value->personal_details->value->first_name->value);
        self::assertSame($newData['product_data'][0]->sku, $data->product_data[0]->sku);
        self::assertSame($newData['product_data'][0]->price, $data->product_data[0]->price);
        self::assertSame($newData['metadata']->key, $data->metadata->key);
        self::assertSame(100, $data->commission);
        self::assertSame(50, $data->partner_commission);
        self::assertSame($newData['merchant_reference'], $data->merchant_reference);
        self::assertSame($newData['merchant_response_url'], $data->merchant_response_url);
        self::assertSame($newData['merchant_checkout_url'], $data->merchant_checkout_url);
        self::assertSame($newData['merchant_redirect_url'], $data->merchant_redirect_url);
        self::assertObjectHasAttribute('application_form_url', $data);
        self::assertObjectHasAttribute('finance_settings', $data);
        self::assertObjectHasAttribute('terms', $data);
        self::assertSame($newData['merchant_finance_option_id'], $data->merchant_finance_option_id);
        self::assertSame($newData['available_finance_options'][0], $data->available_finance_options[0]);
        self::assertObjectHasAttribute('pin_code', $data);
        self::assertObjectHasAttribute('cancelled_amount', $data);
        self::assertObjectHasAttribute('cancelled_amount_total', $data);
        self::assertObjectHasAttribute('cancelable_amount', $data);
        self::assertObjectHasAttribute('cancelled_amount', $data);
        self::assertObjectHasAttribute('cancelled_amount_total', $data);
        self::assertObjectHasAttribute('activatable_amount', $data);
        self::assertObjectHasAttribute('cancelled_amount', $data);
        self::assertObjectHasAttribute('cancelled_amount_total', $data);
        self::assertObjectHasAttribute('refundable_amount', $data);
        self::assertObjectHasAttribute('signer_collection_id', $data);
        self::assertObjectHasAttribute('created_at', $data);
        self::assertObjectHasAttribute('updated_at', $data);

    }
}
