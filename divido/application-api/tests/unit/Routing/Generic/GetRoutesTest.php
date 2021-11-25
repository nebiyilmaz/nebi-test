<?php

namespace Divido\Test\Unit\Routing\Generic;

use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\ResponseSchemas\HealthSchema;
use Divido\Routing\Generic\GetRoutes;
use Divido\Services\Health\Health;
use Divido\Services\Health\HealthService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Noodlehaus\Config;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function test_GetHealth_ReturnsCorrectResponse_OnSuccess()
    {
        $health = new Health();
        $health->setCheckedAt((new \DateTime()));

        $mockHealthService = \Mockery::spy(HealthService::class);
        $mockHealthService->shouldReceive('check')
            ->once()
            ->with()
            ->andReturn($health);

        $this->container['Service.Health'] = $mockHealthService;

        $getRoutes = new GetRoutes($this->container);

        $request = $this->createRequest('get');
        $response = $getRoutes->health($request, new Response());

        $response->getBody()->rewind();

        $schema = new HealthSchema();
        $data = $schema->getData($health);

        self::assertEquals('application-api', $data['service']);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals(json_encode(['data' => $data]), $response->getBody()->getContents());
    }

    public function test_GetDependencies_ReturnsCorrectResponse_OnSuccess()
    {
        $mockConfigService = \Mockery::spy(Config::class);

        $mockConfigService->shouldReceive('get')
            ->once()
            ->with('rpc')
            ->andReturn([
                'user' => '-rpc-user-',
                'host' => '-rpc-host-',
                'vhost' => '-rpc-vhost-'
            ]);

        $mockConfigService->shouldReceive('get')
            ->once()
            ->with('calculation_api.host')
            ->andReturn('-calculation-api-host-');

        $mockConfigService->shouldReceive('get')
            ->once()
            ->with('json_fuse_api.host')
            ->andReturn('-json-fuse-api-host-');

        $mockConfigService->shouldReceive('get')
            ->once()
            ->with('validation_api.host')
            ->andReturn('-validation-api-host-');

        $this->container['Config'] = $mockConfigService;

        $getRoutes = new GetRoutes($this->container);

        $request = $this->createRequest('get');
        $response = $getRoutes->dependencies($request, new Response());

        $json = json_decode($response->getBody()->getContents());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        self::assertIsObject($json->data);
        self::assertIsArray($json->data->dependencies->services);
        self::assertSame('calculator-api-pub', $json->data->dependencies->services[0]->service);
        self::assertSame('json-fuse-api', $json->data->dependencies->services[1]->service);
        self::assertSame('validation-api-pub', $json->data->dependencies->services[2]->service);
        self::assertSame('application-submission-api', $json->data->dependencies->services[3]->service);
        self::assertSame('lender-communication-api', $json->data->dependencies->services[4]->service);
        self::assertSame('merchant-api', $json->data->dependencies->services[5]->service);
        self::assertSame('waterfall-api', $json->data->dependencies->services[6]->service);
    }

    public function test_Exception_OnSuccess()
    {
        $mockService = \Mockery::spy(HealthService::class);

        $this->container['Service.Health'] = $mockService;

        $getRoutes = new GetRoutes($this->container);

        $request = $this->createRequest('get');

        $exception = null;

        try {
            $getRoutes->exception($request, new Response());
        } catch(\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(IncorrectApplicationStatusException::class, $exception);

    }
}
