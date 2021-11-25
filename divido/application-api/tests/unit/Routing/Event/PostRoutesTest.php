<?php

namespace Divido\Test\Unit\Routing\Event;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Event\PostRoutes;
use Divido\Services\Event\EventDispatcherService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PostRoutesTest extends RouteTestCase
{
    public function test_Event_WhenInvalidEvent()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', [], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->newEvent($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_Event_WhenDataMissing()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'event' => 'submission',
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(EventDispatcherService::class);

        $mockService->shouldReceive('dispatcher')
            ->once()
            ->andReturn(true);

        $this->container['Service.EventDispatcher'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->newEvent($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertObjectHasAttribute('executed_at', $json->data);

    }

    public function test_Event_SubmissionSuccess()
    {
        $id = '-uuid-';

        $newData = [
            'event' => 'submission',
            'data' => (object) [
                'id' => $id
            ]
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(EventDispatcherService::class);

        $mockService->shouldReceive('dispatcher')
            ->once()
            ->andReturn(true);

        $this->container['Service.EventDispatcher'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->newEvent($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertObjectHasAttribute('executed_at', $json->data);

    }

    public function test_Event_CancellationSuccess()
    {
        $id = '-uuid-';

        $newData = [
            'event' => 'cancellation',
            'data' => (object) [
                'id' => $id
            ]
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(EventDispatcherService::class);

        $mockService->shouldReceive('dispatcher')
            ->once()
            ->andReturn(true);

        $this->container['Service.EventDispatcher'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->newEvent($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertObjectHasAttribute('executed_at', $json->data);

    }

    public function test_Event_ActivationSuccess()
    {
        $id = '-uuid-';

        $newData = [
            'event' => 'activation',
            'data' => (object) [
                'id' => $id
            ]
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(EventDispatcherService::class);

        $mockService->shouldReceive('dispatcher')
            ->once()
            ->andReturn(true);

        $this->container['Service.EventDispatcher'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->newEvent($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertObjectHasAttribute('executed_at', $json->data);

    }

    public function test_Event_RefundSuccess()
    {
        $id = '-uuid-';

        $newData = [
            'event' => 'refund',
            'data' => (object) [
                'id' => $id
            ]
        ];

        $request = $this->createRequest('post', [], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(EventDispatcherService::class);

        $mockService->shouldReceive('dispatcher')
            ->once()
            ->andReturn(true);

        $this->container['Service.EventDispatcher'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->newEvent($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertObjectHasAttribute('executed_at', $json->data);

    }
}
