<?php

namespace Divido\Test\Unit\Routing\AlternativeOffer;

use DateTime;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\AlternativeOffer\PatchRoutes;
use Divido\Services\AlternativeOffer\AlternativeOffer;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new AlternativeOffer())
            ->setId($id)
            ->setLenderId('-lender-id-')
            ->setApplicationId('-app-uuid-')
            ->setData((object) ['offer'=>(object) ['id' => '-alt-offer-id']])
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

        $mockService = \Mockery::spy(AlternativeOfferService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.AlternativeOffer'] = $mockService;

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
            'data' => (object) ['offer'=>(object) ['id'=>'--id--']]
        ];

        $mockModel = $this->getModel($id);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(AlternativeOfferService::class);
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

        $this->container['Service.AlternativeOffer'] = $mockService;

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
        self::assertsame($newData['data']->offer->id, $data->data->offer->id);
    }
}
