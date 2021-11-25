<?php

namespace Divido\Test\Unit\Routing\Submission;

use DateTime;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\Submission\PatchRoutes;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new Submission())
            ->setId($id)
            ->setApplicationId('-app-uuid-')
            ->setOrder(1)
            ->setDeclineReferred(false)
            ->setLenderId('-lender-uuid-')
            ->setApplicationAlternativeOfferId('-app-alt-offer-id-')
            ->setMerchantFinancePlanId('-merchant-finance-plan-id-')
            ->setStatus('ACCEPTED')
            ->setLenderReference('-lender-reference-')
            ->setLenderLoanReference('-lender-loan-reference-')
            ->setLenderStatus('-lender-status-')
            ->setLenderLoanReference('-lender-loan-reference-')
            ->setLenderStatus('-lender-status-')
            ->setLenderCode('-lender-code-')
            ->setLenderData((object) ['lender_data'=>true])
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_Update_WhenNoData()
    {
        $id = '-uuid-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('put', ['id' => $id], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->patch($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_Update_WhenWrongApplicationId()
    {
        $id = '-uuid-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('get', ['id' => $id], [], [], json_encode(['data' => (object) []]));

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Submission'] = $mockService;

        $exception = null;

        try {
            $routes->patch($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_Update_Success()
    {
        $id = '-uuid-';

        $newData = [
            'order' => 2,
            'decline_referred' => true,
            'application_alternative_offer_id' => '-updated-alt-offer-id-',
            'merchant_finance_plan_id' => '-updated-finance-plan-id-',
            'status' => 'DECLINED',
            'lender_reference' => '-updated-lender-reference-',
            'lender_loan_reference' => '-updated-lender-loan-reference-',
            'lender_status' => '-updated-lender-status-',
            'lender_data' => (object) ['updated'=>true]
        ];

        $mockModel = $this->getModel($id);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $mockService->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($model) {
                return $model;
            });

        $this->container['Service.Submission'] = $mockService;

        $routes = new PatchRoutes($this->container);

        $response = $routes->patch($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;

        self::assertSame($id, $data->id);
        self::assertSame($mockModel->getApplicationId(), $data->application_id);
        self::assertsame($newData['order'], $data->order);
        self::assertsame($newData['decline_referred'], $data->decline_referred);
        self::assertsame($newData['application_alternative_offer_id'], $data->application_alternative_offer_id);
        self::assertsame($newData['merchant_finance_plan_id'], $data->merchant_finance_plan_id);
        self::assertsame($newData['status'], $data->status);
        self::assertsame($newData['lender_reference'], $data->lender_reference);
        self::assertsame($newData['lender_loan_reference'], $data->lender_loan_reference);
        self::assertsame($newData['lender_status'], $data->lender_status);
        self::assertsame($newData['lender_data']->updated, $data->lender_data->updated);
    }
}
