<?php

namespace Divido\Test\Unit\Routing\LenderCall;

use Divido\Routing\LenderCall\GetRoutes;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function test_Call_ReturnJsonSuccess()
    {
        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-']);

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($callName != 'custom-call') return false;
                if ($method != 'GET') return false;

                return true;
            })
            ->andReturn((object) ['type' => 'json', 'data' => json_encode((object) ['returned_data' => true])]);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->call($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame(true, $json->data->returned_data);
    }

    public function test_Call_ReturnHtmlSuccess()
    {
        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-']);

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($callName != 'custom-call') return false;
                if ($method != 'GET') return false;

                return true;
            })
            ->andReturn((object) ['type' => 'html', 'data' => '<b>Hello</b>']);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->call($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $content = $response->getBody()->getContents();

        self::assertSame('<b>Hello</b>', $content);
    }

    public function test_Call_ReturnNullSuccess()
    {
        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-']);

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($callName != 'custom-call') return false;
                if ($method != 'GET') return false;

                return true;
            })
            ->andReturn((object) []);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->call($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame(null, $json->data);
    }

    public function test_Query_Success()
    {
        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['applicationSubmissionId' => '-submission-id-']);

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('query')
            ->once()
            ->withArgs(function ($applicationSubmission) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);

                return true;
            })
            ->andReturn((object) ['status'=>'ACCEPTED']);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->query($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame('ACCEPTED', $json->data->status);
    }
}
