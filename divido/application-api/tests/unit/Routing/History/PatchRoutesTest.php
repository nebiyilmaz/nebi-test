<?php

namespace Divido\Test\Unit\Routing\History;

use DateTime;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\History\PatchRoutes;
use Divido\Services\History\History;
use Divido\Services\History\HistoryService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new History())
            ->setId($id)
            ->setApplicationId('-app-uuid-')
            ->setType('comment')
            ->setStatus('ACCEPTED')
            ->setUser('-user-uuid-')
            ->setSubject('-subject-')
            ->setText('-text-')
            ->setInternal(0)
            ->setDate(new DateTime())
            ->setIpAddress('-ip-address-')
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

        $mockService = \Mockery::spy(HistoryService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.ApplicationHistory'] = $mockService;

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
            'internal' => true
        ];

        $mockModel = $this->getModel($id);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(HistoryService::class);
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

        $this->container['Service.ApplicationHistory'] = $mockService;

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
        self::assertsame($newData['internal'], $data->internal);

    }
}
