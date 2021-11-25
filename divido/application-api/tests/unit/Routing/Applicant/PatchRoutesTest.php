<?php

namespace Divido\Test\Unit\Routing\Applicant;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Exceptions\ApplicationApiException;
use Divido\Proxies\JsonFuse;
use Divido\Routing\Applicant\PatchRoutes;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function test_UpdateApplicants_WhenNoData()
    {
        $token = '-token-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->applicants($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_UpdateApplicants_WhenApplicationNotFound()
    {
        $token = '-token-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) []]));

        $mockJsonFuse = \Mockery::spy(JsonFuse::class);
        $this->container['Proxy.JsonFuse'] = $mockJsonFuse;

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $exception = null;

        try {
            $routes->applicants($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_UpdateApplicants_WhenJsonFuseThrowsAnException()
    {
        $token = '-token-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) []]));

        $mockJsonFuse = \Mockery::spy(JsonFuse::class);
        $mockJsonFuse->shouldReceive('fuse')
            ->once()
            ->andThrow(ApplicationApiException::class, 'unknown error', '123456');

        $this->container['Proxy.JsonFuse'] = $mockJsonFuse;

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->once()
            ->andReturn((object) ['value' => 'applicant']);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $exception = null;

        try {
            $routes->applicants($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ApplicationApiException::class, $exception);
        self::assertSame(123456, $exception->getCode());
    }

    public function test_UpdateApplicants_Success()
    {
        $token = '-token-';

        $fuseReturn = (object) [
            'value' => [
                (object) [
                    'personal_details' => (object) [
                        'value' => (object) [
                            'first_name' => (object) [
                                'value' => 'Ann'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) ['value' => ['applicant']]]));

        $mockJsonFuse = \Mockery::spy(JsonFuse::class);
        $mockJsonFuse->shouldReceive('fuse')
            ->once()
            ->withArgs(function ($applicants, $payload) {
                self::assertIsObject($applicants);
                self::assertIsObject($payload);

                return true;
            })
            ->andReturn($fuseReturn);

        $this->container['Proxy.JsonFuse'] = $mockJsonFuse;

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->twice()
            ->andReturn($fuseReturn);

        $mockModel->shouldReceive('setFormData')
            ->once();
        $mockModel->shouldReceive('setApplicants')
            ->once();

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andReturn($mockModel);

        $mockService->shouldReceive('update')
            ->once()
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $exception = null;

        $response = $routes->applicants($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame($fuseReturn->value[0]->personal_details->value->first_name->value, $json->data->value[0]->personal_details->value->first_name->value);

    }

    public function test_UpdateFormData_WhenNoData()
    {
        $token = '-token-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->formData($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_UpdateFormData_WhenApplicationNotFound()
    {
        $token = '-token-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) []]));

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Application'] = $mockService;

        $exception = null;

        try {
            $routes->formData($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_UpdateFormData_OnSuccess()
    {
        $token = '-token-';

        $fuseReturn = (object) [
            'value' => [
                (object) [
                    'value' => (object) [
                        'personal_details' => (object) [
                            'value' => (object) [
                                'first_name' => (object) [
                                    'value' => 'Ann'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) ['firstName' => 'Ann']]));

        $mockJsonFuse = \Mockery::spy(JsonFuse::class);
        $mockJsonFuse->shouldReceive('fuse')
            ->once()
            ->andThrow(ApplicationApiException::class, 'unknown error', '123456');

        $this->container['Proxy.JsonFuse'] = $mockJsonFuse;

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->once()
            ->andReturn($fuseReturn);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andReturn($mockModel);

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getFormData')
            ->once()
            ->andReturn((object) ['firstName' => 'Ann']);

        $mockService->shouldReceive('update')
            ->once()
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $response = $routes->formData($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame($fuseReturn->value[0]->value->personal_details->value->first_name->value, $json->data->firstName);
    }

    public function test_UpdateFormData_FormDataInWrongPossitionOnSuccess()
    {
        $token = '-token-';

        $fuseReturn = (object) [
            'value' => [
                (object) [
                    'value' => (object) [
                        'personal_details' => (object) [
                            'value' => (object) [
                                'first_name' => (object) [
                                    'value' => 'Ann'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('patch', ['token' => $token], [], [], json_encode(['data' => (object) ['formData'=>(object) ['firstName' => 'Ann']]]));

        $mockJsonFuse = \Mockery::spy(JsonFuse::class);
        $mockJsonFuse->shouldReceive('fuse')
            ->once()
            ->andReturn($fuseReturn);

        $this->container['Proxy.JsonFuse'] = $mockJsonFuse;

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getApplicants')
            ->once()
            ->andReturn($fuseReturn);

        $mockService = \Mockery::spy(ApplicationService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andReturn($mockModel);

        $mockModel = \Mockery::spy(Application::class);
        $mockModel->shouldReceive('getFormData')
            ->once()
            ->andReturn((object) ['firstName' => 'Ann']);

        $mockService->shouldReceive('update')
            ->once()
            ->andReturn($mockModel);

        $this->container['Service.Application'] = $mockService;

        $response = $routes->formData($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame($fuseReturn->value[0]->value->personal_details->value->first_name->value, $json->data->firstName);
    }
}
