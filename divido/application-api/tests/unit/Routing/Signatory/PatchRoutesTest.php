<?php

namespace Divido\Test\Unit\Routing\Signatory;

use DateTime;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Routing\Signatory\PatchRoutes;
use Divido\Services\Signatory\Signatory;
use Divido\Services\Signatory\SignatoryService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class PatchRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new Signatory())
            ->setId($id)
            ->setApplicationId('-app-uuid-')
            ->setFirstName('Ann')
            ->setLastName('Heselden')
            ->setEmailAddress('ann.heselden@gmail.com')
            ->setTitle('Mrs')
            ->setDateOfBirth(new DateTime('1954-05-23'))
            ->setLenderReference('-lender-reference-')
            ->setHostedSigning(true)
            ->setDataRaw((object) ['data'=>true])
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_Update_WhenNoData()
    {
        $id = '-uuid-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('put', ['id' => $id], [], [], json_encode([]));

        $exception = null;

        try {
            $routes->patch($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PayloadPropertyMissingOrInvalidException::class, $exception);
        self::assertArrayHasKey('property', $exception->getContext());
        self::assertArrayHasKey('more', $exception->getContext());
        self::assertSame(400002, $exception->getCode());
    }

    public function test_Update_WhenWrongApplicationId()
    {
        $id = '-uuid-';

        $routes = new PatchRoutes($this->container);

        $request = $this->createRequest('get', ['id' => $id], [], [], json_encode(['data' => (object) []]));

        $mockService = \Mockery::spy(SignatoryService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Signatory'] = $mockService;

        $exception = null;

        try {
            $routes->patch($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_Update_Success()
    {
        $id = '-uuid-';

        $newData = [
            'first_name' => '-updated_first_name-',
            'last_name' => '-updated_last_name-',
            'email_address' => '_updated_email_address_',
            'title' => '_updated_title_',
            'date_of_birth' => '1980-01-01',
            'lender_reference' => '_updated_lender_reference_',
            'hosted_signing' => false,
            'data_raw'=>(object) ['updated'=>true]
        ];

        $mockModel = $this->getModel($id);

        $request = $this->createRequest('patch', ['id' => $id], [], [], json_encode(['data' => (object) $newData]));

        $mockService = \Mockery::spy(SignatoryService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $mockService->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($model) {
                return $model;
            });

        $this->container['Service.Signatory'] = $mockService;

        $routes = new PatchRoutes($this->container);

        $response = $routes->patch($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;

        self::assertSame($id, $data->id);
        self::assertsame($newData['first_name'], $data->first_name);
        self::assertsame($newData['last_name'], $data->last_name);
        self::assertsame($newData['email_address'], $data->email_address);
        self::assertsame($newData['title'], $data->title);
        self::assertsame($newData['date_of_birth'], $data->date_of_birth);
        self::assertsame($newData['lender_reference'], $data->lender_reference);
        self::assertsame($newData['hosted_signing'], $data->hosted_signing);
        self::assertsame($newData['data_raw']->updated, $data->data_raw->updated);

    }
}
