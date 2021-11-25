<?php

namespace Divido\Test\Unit\Routing\LenderCall;

use Divido\Routing\LenderCall\DeleteRoutes;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class DeleteRoutesTest extends RouteTestCase
{
    public function test_Call_ReturnJsonSuccess()
    {
        $routes = new DeleteRoutes($this->container);

        $request = $this->createRequest('get', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-']);

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($callName != 'custom-call') return false;
                if ($method != 'DELETE') return false;

                return true;
            })
            ->andReturn((object) ['success' => true]);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->call($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());
        self::assertSame(true, $json->data->success);
    }
}
