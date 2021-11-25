<?php

namespace Divido\Routing\Submission;

use Divido\LogStreamer\Logger;
use Divido\ResponseSchemas\SubmissionSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Throwable;

/**
 * Class PostRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @param $payload
     * @return Submission
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Submission();
        $model->setOrder($payload->order)
            ->setDeclineReferred($payload->decline_referred)
            ->setLenderId($payload->lender_id)
            ->setApplicationAlternativeOfferId($payload->application_alternative_offer_id)
            ->setMerchantFinancePlanId($payload->merchant_finance_plan_id ?? null)
            ->setStatus($payload->status ?? "UNSUBMITTED")
            ->setLenderReference($payload->lender_reference ?? "")
            ->setLenderLoanReference($payload->lender_loan_reference ?? "")
            ->setLenderStatus($payload->lender_status ?? "PROPOSAL")
            ->setLenderData($payload->lender_data ?? (object) []);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/submissions",
     *     tags={"Submission"},
     *     description="Creates a new submission",
     *     @OA\Parameter(
     *         name="x-divido-tenant-id",
     *         in="header",
     *         required=true,
     *         description="The tenant",
     *         @OA\Schema(
     *             type="string",
     *             example="divido"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="application",
     *         in="path",
     *         required=true,
     *         description="The application id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Submission",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_submission.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ok"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_submission');

            $applicationId = $request->getAttribute('applicationId');

            $model = $this->mapPayloadToModel($payload->data);
            $model->setApplicationId($applicationId);

            /** @var SubmissionService $service */
            $service = $this->container->get('Service.Submission');
            $model = $service->create($model);

            $schema = new SubmissionSchema();

            $data = $schema->getData($model);

            return $this->json(['data' => $data], $response);
        } catch (Throwable $e) {
            $level = 'error';
            $code = $e->getCode();
            $message = $e->getMessage();

            throw $e;
        } finally {
            /** @var Logger $logger */
            $logger = $this->container->get('Logger');

            $logger->log(
                $level ?? 'info',
                'Creating submission',
                [
                    'status_code' => $code ?? StatusCode::HTTP_OK,
                    'application_id' => $applicationId ?? '',
                    'request' => $payload ?? '',
                    'response' => $data ?? '',
                    'error_message' => $message ?? '',
                ]
            );
        }
    }
}
