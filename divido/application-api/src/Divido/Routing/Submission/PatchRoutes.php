<?php

namespace Divido\Routing\Submission;

use Divido\ResponseSchemas\SubmissionSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PatchRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param Submission $model
     * @param $payload
     * @return Submission
     */
    private function mapPayloadToModel(Submission $model, $payload)
    {
        if (key_exists('order', $payload)) {
            $model->setOrder($payload->order);
        }

        if (key_exists('decline_referred', $payload)) {
            $model->setDeclineReferred($payload->decline_referred);
        }

        if (key_exists('application_alternative_offer_id', $payload)) {
            $model->setApplicationAlternativeOfferId($payload->application_alternative_offer_id);
        }

        if (key_exists('merchant_finance_plan_id', $payload)) {
            $model->setMerchantFinancePlanId($payload->merchant_finance_plan_id);
        }

        if (key_exists('status', $payload)) {
            $model->setStatus($payload->status);
        }

        if (key_exists('lender_reference', $payload)) {
            $model->setLenderReference($payload->lender_reference);
        }

        if (key_exists('lender_loan_reference', $payload)) {
            $model->setLenderLoanReference($payload->lender_loan_reference);
        }

        if (key_exists('lender_status', $payload)) {
            $model->setLenderStatus($payload->lender_status);
        }

        if (key_exists('lender_data', $payload)) {
            $model->setLenderData($payload->lender_data);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/submissions/{submission}",
     *     tags={"Submission"},
     *     description="Updates a submission",
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
     *         name="submission",
     *         in="path",
     *         required=true,
     *         description="The submission",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Submission",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_submission.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
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
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_submission');

        $model = new Submission();
        $model->setId($id);

        /** @var SubmissionService $service */
        $service = $this->container->get('Service.Submission');
        $model = $service->getOne($model);

        $model = $this->mapPayloadToModel($model, $payload->data);

        $model = $service->update($model);

        /** @var SubmissionSchema $schema */
        $schema = new SubmissionSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
