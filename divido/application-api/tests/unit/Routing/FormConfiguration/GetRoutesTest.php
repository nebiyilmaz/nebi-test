<?php

namespace Divido\Test\Unit\Routing\FormConfiguration;

use Divido\Routing\FormConfiguration\GetRoutes;
use Divido\Services\Application\Application;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\Tenant\Tenant;
use Divido\Services\Tenant\TenantService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function test_Render_Success()
    {
        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['token' => '-token-'], [], [], json_encode([]));

        $mockService = \Mockery::spy(FormConfigurationService::class);

        $mockService->shouldReceive('render')
            ->once()
            ->withArgs(function ($model, $useReadReplica) {
                self::assertSame(false, $useReadReplica);
                self::assertInstanceOf(Application::class, $model);

                return true;
            })
            ->andReturn(['formConfiguration' => ['formConfiguration'=>true],'application'=>['id'=>'-id-']]);

        $this->container['Service.FormConfiguration'] = $mockService;

        $response = $routes->render($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame(true, $json->data->formConfiguration);
        self::assertSame('-id-', $json->meta->application->id);
    }

    public function test_Index_Success()
    {
        $tenantId = 'divido';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['token' => '-token-'], [], [], json_encode([]));

        $mockTenantService = \Mockery::spy(ServerRequestInterface::class);
        $mockTenantService->shouldReceive('get')
            ->once()
            ->withArgs(function ($constant) {
                self::assertSame('PARSED_TENANT_ID', $constant);

                return true;
            })
            ->andReturn($tenantId);

        $this->container['environment'] = $mockTenantService;

        $mockTenantModel = \Mockery::spy(Tenant::class);
        $mockTenantModel->shouldReceive('getSettings')
            ->once()
            ->andReturn([
                'application_form' => [
                    'default_page' => [
                        'component' => 'component'
                    ]
                ]
            ]);

        $mockTenantService = \Mockery::spy(TenantService::class);

        $mockTenantService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($env) use ($tenantId) {
                self::assertSame($env, $tenantId);

                return true;
            })
            ->andReturn($mockTenantModel);

        $this->container['Service.PlatformEnvironment'] = $mockTenantService;

        $response = $routes->index($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame('component', $json->data);

    }
}
