<?php

namespace Divido\Test\Unit\Routing\Submission;

use DateTime;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\Routing\Submission\GetRoutes;
use Divido\Services\Application\Application;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Slim\Http\Response;

class GetRoutesTest extends RouteTestCase
{
    public function getModel($id = null)
    {
        if (!$id) {
            $id = '-uuid-';
        }

        return (new Submission())
            ->setId($id)
            ->setApplicationId('-app-uuid-')
            ->setOrder(1)
            ->setDeclineReferred(false)
            ->setLenderId('-lender-uuid-')
            ->setApplicationAlternativeOfferId('-app-alt-offer-id-')
            ->setMerchantFinancePlanId('-merchant-finance-plan-id-')
            ->setStatus('ACCEPTED')
            ->setLenderReference('-lender-reference-')
            ->setLenderLoanReference('-lender-loan-reference-')
            ->setLenderStatus('-lender-status-')
            ->setLenderLoanReference('-lender-loan-reference-')
            ->setLenderStatus('-lender-status-')
            ->setLenderCode('-lender-code-')
            ->setLenderData((object) ['lender_data' => true])
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());
    }

    public function test_GetOne_WhenResourceNotFound()
    {
        $id = '-uuid-';

        $routes = new GetRoutes($this->container);

        $request = $this->createRequest('get', ['id' => $id]);

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andThrow(ResourceNotFoundException::class);

        $this->container['Service.Submission'] = $mockService;

        $exception = null;

        try {
            $routes->getOne($request, new Response());
        } catch (\Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ResourceNotFoundException::class, $exception);
        self::assertArrayHasKey('identifier', $exception->getContext());
        self::assertArrayHasKey('key', $exception->getContext());
        self::assertSame(404001, $exception->getCode());
    }

    public function test_GetOne_Success()
    {
        $id = '-uuid-';

        $request = $this->createRequest('get', ['id' => $id]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Submission'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getOne($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertSame($mockModel->getId(), $data->id);
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getOrder(), $data->order);
        self::assertSame($mockModel->getApplicationId(), $data->application_id);
        self::assertSame($mockModel->getLenderId(), $data->lender_id);
        self::assertSame($mockModel->getApplicationAlternativeOfferId(), $data->application_alternative_offer_id);
        self::assertSame($mockModel->getMerchantFinancePlanId(), $data->merchant_finance_plan_id);
        self::assertSame($mockModel->getLenderReference(), $data->lender_reference);
        self::assertSame($mockModel->getLenderLoanReference(), $data->lender_loan_reference);
        self::assertSame($mockModel->getLenderStatus(), $data->lender_status);
        self::assertSame($mockModel->getLenderData()->lender_data, $data->lender_data->lender_data);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }

    public function test_GetOneWithExtra_Success()
    {
        $id = '-uuid-';

        $request = $this->createRequest('get', ['id' => $id], ['extra' => true]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getOne')
            ->once()
            ->withArgs(function ($model) use ($id) {
                if ($id != $model->getId()) return false;

                return true;
            })
            ->andReturn($mockModel);

        $this->container['Service.Submission'] = $mockService;

        $mockSubmissionObjectBuilder = \Mockery::spy(SubmissionObjectBuilder::class);
        $mockSubmissionObjectBuilder->shouldReceive('getSubmission')
            ->once()
            ->withArgs(function ($applicationModel, $submissionModel) {
                self::assertInstanceOf(Application::class, $applicationModel);
                self::assertInstanceOf(Submission::class, $submissionModel);

                return true;
            })
            ->andReturnUsing(function () use ($id) {
                return (object) [
                    'id' => $id
                ];
            });

        $this->container['Helper.SubmissionObjectBuilder'] = $mockSubmissionObjectBuilder;

        $routes = new GetRoutes($this->container);

        $response = $routes->getOne($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        self::assertSame($id, $json->data->id);
    }

    public function test_GetAll_WhenNoRecordsExists()
    {
        $applicationId = '-app-uuid-';
        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([]);

        $this->container['Service.Submission'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        $data = $json->data;
        self::assertIsArray($data);
        self::assertCount(0, $data);
    }

    public function test_GetAll_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-uuid-';

        $request = $this->createRequest('getAll', ['applicationId' => $applicationId]);

        $mockModel = $this->getModel($id);

        $mockService = \Mockery::spy(SubmissionService::class);
        $mockService->shouldReceive('getAll')
            ->once()
            ->andReturn([$mockModel]);

        $this->container['Service.Submission'] = $mockService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        self::assertIsArray($json->data);
        self::assertCount(1, $json->data);
        self::assertIsObject($json->data[0]);
        $data = $json->data[0];
        self::assertSame($mockModel->getId(), $data->id);
        self::assertSame($mockModel->getStatus(), $data->status);
        self::assertSame($mockModel->getOrder(), $data->order);
        self::assertSame($mockModel->getApplicationId(), $data->application_id);
        self::assertSame($mockModel->getLenderId(), $data->lender_id);
        self::assertSame($mockModel->getApplicationAlternativeOfferId(), $data->application_alternative_offer_id);
        self::assertSame($mockModel->getMerchantFinancePlanId(), $data->merchant_finance_plan_id);
        self::assertSame($mockModel->getLenderReference(), $data->lender_reference);
        self::assertSame($mockModel->getLenderLoanReference(), $data->lender_loan_reference);
        self::assertSame($mockModel->getLenderStatus(), $data->lender_status);
        self::assertSame($mockModel->getLenderData()->lender_data, $data->lender_data->lender_data);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->created_at);
        self::assertSame($mockModel->getUpdatedAt()->format("c"), $data->updated_at);
    }

    public function test_GetAllWithExtra_Success()
    {
        $id = '-uuid-';
        $applicationId = '-app-uuid-';

        $request = $this->createRequest('getAll', ['applicationId' => $applicationId], ['extra' => true]);

        $mockSubmissionObjectBuilder = \Mockery::spy(SubmissionObjectBuilder::class);
        $mockSubmissionObjectBuilder->shouldReceive('getAllSubmissions')
            ->once()
            ->withArgs(function ($applicationModel) {
                self::assertInstanceOf(Application::class, $applicationModel);

                return true;
            })
            ->andReturnUsing(function () use ($id) {
                return [
                    (object) [
                        'id' => $id
                    ]
                ];
            });

        $this->container['Helper.SubmissionObjectBuilder'] = $mockSubmissionObjectBuilder;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody()->getContents());

        self::assertIsObject($json);
        self::assertObjectHasAttribute('data', $json);
        self::assertIsArray($json->data);
        self::assertCount(1, $json->data);
        self::assertIsObject($json->data[0]);
        $data = $json->data[0];
        self::assertSame($id, $data->id);

    }
}
