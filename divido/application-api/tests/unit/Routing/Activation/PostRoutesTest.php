<?php

namespace Divido\Test\Unit\Routing\Activation;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Activation\PostRoutes;
use Divido\Services\Activation\ActivationService;
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
            'reference' => '-reference-',
            'amount' => 100000,
            'product_data' => [(object) ['sku' => '-sku-', 'price' => 100000]],
            'delivery_method' => '-delivery-method-',
            'tracking_number' => '-tracking-number-',
            'comment' => '-comment-'
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(ActivationService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.Activation'] = $mockService;

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
        self::assertsame($newData['reference'], $data->reference);
        self::assertsame($newData['amount'], $data->amount);
        self::assertsame($newData['product_data'][0]->sku, $data->product_data[0]->sku);
        self::assertsame($newData['delivery_method'], $data->delivery_method);
        self::assertsame($newData['tracking_number'], $data->tracking_number);
        self::assertsame($newData['comment'], $data->comment);

    }

    public function testResponseIsSuccessWhenSomePropertiesAreNotSent()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'status' => '-status-',
            'amount' => 100000,
            'product_data' => [(object) ['sku' => '-sku-', 'price' => 100000]],
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(ActivationService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.Activation'] = $mockService;

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
        self::assertsame('', $data->reference);
        self::assertsame($newData['amount'], $data->amount);
        self::assertsame($newData['product_data'][0]->sku, $data->product_data[0]->sku);
        self::assertsame('', $data->delivery_method);
        self::assertsame('', $data->tracking_number);
        self::assertsame('', $data->comment);
    }
}
