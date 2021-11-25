<?php

namespace Divido\Test\Unit\Routing\History;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\History\PostRoutes;
use Divido\Services\History\HistoryService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PostRoutesTest extends RouteTestCase
{
    public function test_CreateComment_WhenNoData()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', [], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->createComment($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_CreateComment_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'status' => '-status-',
            'user' => '-user-id-',
            'subject' => '-subject-',
            'text' => '-text-',
            'internal' => false,
            'date' => '2019-01-01 12:00:01',
            'ip_address' => '-ip-address-'
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(HistoryService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.ApplicationHistory'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->createComment($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($id, $data->id);
        self::assertSame($applicationId, $data->application_id);
        self::assertsame($newData['status'], $data->status);
        self::assertsame('comment', $data->type);
        self::assertsame($newData['user'], $data->user);
        self::assertsame($newData['subject'], $data->subject);
        self::assertsame($newData['text'], $data->text);
        self::assertsame($newData['internal'], $data->internal);
        self::assertsame(substr($newData['date'], 0, 10), $data->date);
        self::assertsame($newData['ip_address'], $data->ip_address);

    }

    public function test_CreateStatus_WhenNoData()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', [], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->createStatus($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_CreateStatus_InvalidStatus()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'status' => 'INVALID_STATUS',
            'user' => '-user-id-',
            'subject' => '-subject-',
            'text' => '-text-',
            'internal' => false,
            'date' => '2019-01-01 12:00:01',
            'ip_address' => '-ip-address-'
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $routes = new PostRoutes($this->container);

        $exception = null;

        try {
            $routes->createStatus($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);

    }

    public function test_CreateStatus_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'status' => 'REFERRED',
            'user' => '-user-id-',
            'subject' => '-subject-',
            'text' => '-text-',
            'internal' => false,
            'date' => '2019-01-01 12:00:01',
            'ip_address' => '-ip-address-'
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(HistoryService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.ApplicationHistory'] = $mockService;

        $routes = new PostRoutes($this->container);

        try {
            $response = $routes->createStatus($request, new Response());
        } catch (\Exception $e) {
            var_dump($e);exit;
        }
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($id, $data->id);
        self::assertSame($applicationId, $data->application_id);
        self::assertsame($newData['status'], $data->status);
        self::assertsame('status', $data->type);
        self::assertsame($newData['user'], $data->user);
        self::assertsame($newData['subject'], $data->subject);
        self::assertsame($newData['text'], $data->text);
        self::assertsame($newData['internal'], $data->internal);
        self::assertsame(substr($newData['date'], 0, 10), $data->date);
        self::assertsame($newData['ip_address'], $data->ip_address);

    }
}
