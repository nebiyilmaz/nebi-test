<?php

namespace Divido\Test\Unit\Routing\Deposit;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Deposit\PostRoutes;
use Divido\Services\Deposit\DepositService;
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
            'status' => '-status-',
            'merchant_reference' => '-merchant-reference-',
            'amount' => 100000,
            'product_data' => [(object) ['sku' => '-sku-', 'price' => 100000]],
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(DepositService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.Deposit'] = $mockService;

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
        self::assertsame($newData['status'], $data->status);
        self::assertsame($newData['amount'], $data->amount);
        self::assertsame($newData['product_data'][0]->sku, $data->product_data[0]->sku);
    }
}
