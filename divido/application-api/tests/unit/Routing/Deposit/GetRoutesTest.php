<?php

namespace Divido\Test\Unit\Routing\Deposit;

use DateTime;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\Deposit\GetRoutes;
use Divido\Services\Deposit\Deposit;
use Divido\Services\Deposit\DepositService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new Deposit())
            ->setId($id)
            ->setApplicationId('-app-uuid-')
            ->setType('cash')
            ->setDataRaw((object) [])
            ->setMerchantReference('merchant reference')
            ->setStatus('ACTIVATED')
            ->setAmount(100000)
            ->setReference('reference')
            ->setMerchantComment('comment')
            ->setProductData([(object) ['sku' => 'sku', 'price' => 100000]])
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_GetOne_WhenResourceNotFound()
    {
        $id = '-uuid-';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['id' => $id]);

        $mockService = \Mockery::spy(DepositService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Deposit'] = $mockService;

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

        $mockService = \Mockery::spy(DepositService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Deposit'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getOne($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($mockModel->getId(), $data->id);
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getAmount(), $data->amount);
        self::assertSame($mockModel->getApplicationId(), $data->application_id);
        self::assertSame($mockModel->getReference(), $data->reference);
        self::assertSame($mockModel->getProductData()[0]->sku, $data->product_data[0]->sku);
        self::assertSame($mockModel->getProductData()[0]->price, $data->product_data[0]->price);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }

    public function test_GetAll_WhenNoRecordsExists()
    {
        $applicationId = '-app-uuid-';
        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockService = \Mockery::spy(DepositService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([]);

        $this->container['Service.Deposit'] = $mockService;

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

    public function test_GetAll_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-uuid-';

        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(DepositService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([$mockModel]);

        $this->container['Service.Deposit'] = $mockService;

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
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getAmount(), $data->amount);
        self::assertSame($mockModel->getApplicationId(), $data->application_id);
        self::assertSame($mockModel->getReference(), $data->reference);
        self::assertSame($mockModel->getProductData()[0]->sku, $data->product_data[0]->sku);
        self::assertSame($mockModel->getProductData()[0]->price, $data->product_data[0]->price);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }
}
