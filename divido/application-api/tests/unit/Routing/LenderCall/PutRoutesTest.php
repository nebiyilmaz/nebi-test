<?php

namespace Divido\Test\Unit\Routing\LenderCall;

use Divido\Routing\LenderCall\PutRoutes;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PutRoutesTest extends RouteTestCase
{
    public function test_Call_ReturnJsonSuccess()
    {
        $routes = new PutRoutes($this->container);

        $request = $this->createRequest('put', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($callName != 'custom-call') return false;
                if ($method != 'PUT') return false;

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
