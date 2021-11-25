<?php

namespace Divido\Test\Unit\Routing\Signatory;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\Routing\Signatory\PostRoutes;
use Divido\Services\Signatory\SignatoryService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PostRoutesTest extends RouteTestCase
{
    public function test_Create_WhenNoData()
    {
        $routes = new PostRoutes($this->container);

        $request = $this->createRequest('post', [], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->create($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_Create_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-id-';

        $newData = [
            'first_name' => '-first_name-',
            'last_name' => '-last_name-',
            'email_address' => '_email_address_',
            'title' => '_title_',
            'date_of_birth' => '1980-01-01',
            'lender_reference' => '_lender_reference_',
            'hosted_signing' => false,
            'data_raw'=>(object) ['new'=>true]
        ];

        $request = $this->createRequest('post', ['applicationId' => $applicationId], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(SignatoryService::class);

        $mockService->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($model) use ($id) {
                $model->setId($id)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                return $model;
            });

        $this->container['Service.Signatory'] = $mockService;

        $routes = new PostRoutes($this->container);

        $response = $routes->create($request, new Response());
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($id, $data->id);
        self::assertSame($applicationId, $data->application_id);
        self::assertsame($newData['first_name'], $data->first_name);
        self::assertsame($newData['last_name'], $data->last_name);
        self::assertsame($newData['email_address'], $data->email_address);
        self::assertsame($newData['title'], $data->title);
        self::assertsame($newData['date_of_birth'], $data->date_of_birth);
        self::assertsame($newData['lender_reference'], $data->lender_reference);
        self::assertsame($newData['hosted_signing'], $data->hosted_signing);
        self::assertsame($newData['data_raw']->new, $data->data_raw->new);

    }
}
