<?php

namespace Divido\Test\Unit\Routing\Cancellation;

use DateTime;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\Cancellation\PatchRoutes;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new Cancellation())
            ->setId($id)
            ->setStatus('ACTIVATED')
            ->setAmount(100000)
            ->setApplicationId('-app-uuid-')
            ->setReference('reference')
            ->setComment('comment')
            ->setProductData([(object) ['sku' => 'sku', 'price' => 100000]])
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_Update_WhenNoData()
    {
        $id = '-uuid-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode([]));

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

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) []]));

        $mockService = \Mockery::spy(CancellationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Cancellation'] = $mockService;

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
            'status' => '-updated_status-',
            'reference' => '-updated_reference-',
            'delivery_method' => '-updated_delivery_method-',
            'tracking_number' => '-updated_tracking_number-',
            'comment' => '_updated_comment_'
        ];

        $mockModel = $this->getModel($id);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(CancellationService::class);
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

        $this->container['Service.Cancellation'] = $mockService;

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
        self::assertsame($newData['status'], $data->status);
        self::assertsame($newData['reference'], $data->reference);
        self::assertsame($newData['comment'], $data->comment);
    }
}
