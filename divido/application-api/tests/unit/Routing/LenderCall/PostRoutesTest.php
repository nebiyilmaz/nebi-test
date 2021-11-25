<?php

namespace Divido\Test\Unit\Routing\LenderCall;

use Divido\Routing\LenderCall\PostRoutes;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PostRoutesTest extends RouteTestCase
{
    public function test_Call_ReturnJsonSuccess()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('customCall')
            ->once()
            ->withArgs(function ($applicationSubmission, $callName, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($callName != 'custom-call') return false;
                if ($method != 'POST') return false;

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

    public function test_Notification_ReturnJsonSuccess()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('notification')
            ->once()
            ->withArgs(function ($applicationSubmission, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($method != 'POST') return false;

                return true;
            })
            ->andReturn((object) ['type' => 'json','data'=>json_encode((object) ['success'=>true])]);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->notification($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame(true, $json->data->success);
    }

    public function test_Notification_ReturnXmlSuccess()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('notification')
            ->once()
            ->withArgs(function ($applicationSubmission, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($method != 'POST') return false;

                return true;
            })
            ->andReturn((object) ['type' => 'xml','data'=>'<xml>data</xml>']);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->notification($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        self::assertSame('<xml>data</xml>', $response->getBody()->getContents());
    }

    public function test_Notification_ReturnHtmlSuccess()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('notification')
            ->once()
            ->withArgs(function ($applicationSubmission, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($method != 'POST') return false;

                return true;
            })
            ->andReturn((object) ['type' => 'html','data'=>'<strong>hello</strong>']);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->notification($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        self::assertSame('<strong>hello</strong>', $response->getBody()->getContents());
    }

    public function test_Notification_ReturnNullSuccess()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['callName' => 'custom-call', 'applicationSubmissionId' => '-submission-id-'], ['param'=>'custom-param'], [], json_encode(['payload' => 'custom-payload']));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('notification')
            ->once()
            ->withArgs(function ($applicationSubmission, $method, $params, $payload) {
                self::assertInstanceOf(Submission::class, $applicationSubmission);
                if ($payload->payload != 'custom-payload') return false;
                if ($params['param'] != 'custom-param') return false;
                if ($method != 'POST') return false;

                return true;
            })
            ->andReturn(null);

        $this->container['Service.LenderCall'] = $mockService;

        $response = $routes->notification($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        self::assertSame(null, $json->data);
    }

    public function test_Submit_Success()
    {
        $token = '-token-';

        $data = (object) ['applicants'=>(object) ['value'=>[(object) ['value'=>(object) ['personal_details'=>(object) [
            'value'=>(object) ['first_name'=>(object) ['value'=>'Ann']]
        ]]]]]];

        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', ['token' => $token], [], [], json_encode((object) ['data' => $data]));

        $mockService = \Mockery::spy(LenderCallService::class);

        $mockService->shouldReceive('submit')
            ->once()
            ->withArgs(function ($calledToken, $payload) use ($token, $data) {

                if ($token != $calledToken) return false;
                if ($data->applicants->value[0]->value->personal_details->value->first_name->value !=
                    $payload->applicants->value[0]->value->personal_details->value->first_name->value) return false;

                return true;
            })
            ->andReturn((object) ['success' => true]);

        $this->container['Service.LenderCall'] = $mockService;

        $mockFormConfigurationService = \Mockery::spy(FormConfigurationService::class);

        $mockFormConfigurationService->shouldReceive('render')
            ->once()
            ->andReturn((object) ['formConfiguration'=>(object) ['success' => true],'application'=>['id'=>'-id-']]);

        $this->container['Service.FormConfiguration'] = $mockFormConfigurationService;

        $response = $routes->submit($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());
        self::assertSame(true, $json->data->success);
    }
}
