<?php

namespace Divido\Test\Unit\Routing\AlternativeOffer;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\AlternativeOffer\PostRoutes;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Services\Application\ApplicationService;
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
            'lender_id' => '-lender-id-',
            'data' => (object) ['offer'=>(object) ['id'=>'-id-']],
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockApplicationService = \Mockery::spy(ApplicationService::class);
        $mockApplicationService ->shouldReceive('getOne')
            ->once()
            ->andReturnUsing(function ($model) {
                return $model;
            });

        $this->container['Service.Application'] = $mockApplicationService;

        $mockService = \Mockery::spy(AlternativeOfferService::class);
        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.AlternativeOffer'] = $mockService;

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
        self::assertsame($newData['lender_id'], $data->lender_id);
        self::assertsame($newData['data']->offer->id, $data->data->offer->id);

    }
}
