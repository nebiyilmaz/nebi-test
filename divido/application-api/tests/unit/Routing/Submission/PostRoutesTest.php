<?php

namespace Divido\Test\Unit\Routing\Submission;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Submission\PostRoutes;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\SubmissionService;
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
        $applicationId = '-app-id-';

        $newData = [
            'order' => 2,
            'lender_id' => '-lender-id-',
            'decline_referred' => true,
            'application_alternative_offer_id' => '-alt-offer-id-',
            'merchant_finance_plan_id' => '-finance-plan-id-',
            'status' => 'DECLINED',
            'lender_reference' => '-lender-reference-',
            'lender_loan_reference' => '-lender-loan-reference-',
            'lender_status' => '-lender-status-',
            'lender_data' => (object) ['new'=>true]
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(SubmissionService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $mockLenderCallService = \Mockery::spy(LenderCallService::class);

        $this->container['Service.LenderCall'] = $mockLenderCallService;
        $this->container['Service.Submission'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->create($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($id, $data->id);
        self::assertSame($applicationId, $data->application_id);
        self::assertsame($newData['order'], $data->order);
        self::assertsame($newData['decline_referred'], $data->decline_referred);
        self::assertsame($newData['application_alternative_offer_id'], $data->application_alternative_offer_id);
        self::assertsame($newData['merchant_finance_plan_id'], $data->merchant_finance_plan_id);
        self::assertsame($newData['status'], $data->status);
        self::assertsame($newData['lender_reference'], $data->lender_reference);
        self::assertsame($newData['lender_loan_reference'], $data->lender_loan_reference);
        self::assertsame($newData['lender_status'], $data->lender_status);
        self::assertsame($newData['lender_data']->new, $data->lender_data->new);

    }
}
