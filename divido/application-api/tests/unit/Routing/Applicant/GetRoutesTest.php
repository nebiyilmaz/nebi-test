<?php

namespace Divido\Test\Unit\Routing\Applicant;

use Divido\Routing\Applicant\GetRoutes;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function test_Applicants_Success()
    {
        $token = '-token-';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['token' => $token], [], [], json_encode([]));

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->once()
            ->andReturn((object) ['value'=>'applicants']);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) {
                self::assertInstanceOf(Application::class, $model);

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $response = $routes->applicants($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame('applicants', $json->data->value);
    }

    public function test_FormData_Success()
    {
        $token = '-token-';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['token' => $token], [], [], json_encode([]));

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getFormData')
            ->once()
            ->andReturn((object) [
                'title' => 'Miss',
                'firstName' => 'Ann',
                'lastName' => 'Heselden',
                'phoneNumber' => '07777777777',
                'email' => 'email@aol.com'
            ]);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) {
                self::assertInstanceOf(Application::class, $model);

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $response = $routes->formData($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame('Miss', $json->data->title);
        self::assertSame('Ann', $json->data->firstName);
        self::assertSame('Heselden', $json->data->lastName);
        self::assertSame('07777777777', $json->data->phoneNumber);
        self::assertSame('email@aol.com', $json->data->email);
    }
}
