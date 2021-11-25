<?php

namespace Divido\Test\Unit\Routing\Application;

use Divido\Routing\Application\DeleteRoutes;
use Divido\Services\Application\ApplicationService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class DeleteRoutesTest extends RouteTestCase
{
    public function test_Delete_Success()
    {
        $id = '-uuid-';

        $request = $this->createRequest('get', ['id' => $id]);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->container['Service.Application'] = $mockService;

        $routes = new DeleteRoutes($this->container);

        $response = $routes->delete($request, new Response());

        self::assertEquals(200, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }
}
