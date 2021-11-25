<?php

namespace Divido\Routing\Application;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Proxies\JsonFuse;
use Divido\ResponseSchemas\ApplicationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\LenderFee\LenderFeeService;
use GuzzleHttp\Exception\GuzzleException;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PutRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param Application $model
     * @param $payload
     * @return Application
     */
    private function mapPayloadToModel(Application $model, $payload)
    {
        if (key_exists('status', $payload)) {
            $model->setStatus($payload->status);
        }

        if (key_exists('application_submission_id', $payload)) {
            $model->setApplicationSubmissionId($payload->application_submission_id);
        }

        if (key_exists('merchant_finance_option_id', $payload)) {
            $model->setMerchantFinanceOptionId($payload->merchant_finance_option_id);
        }

        if (key_exists('finalised', $payload)) {
            $model->setFinalised($payload->finalised);
        }

        if (key_exists('purchase_price', $payload)) {
            $model->setPurchasePrice($payload->purchase_price);
        }

        if (key_exists('deposit_amount', $payload)) {
            $model->setDepositAmount($payload->deposit_amount);
        }

        if (key_exists('product_data', $payload)) {
            $model->setProductData($payload->product_data);
        }

        if (key_exists('metadata', $payload)) {
            $model->setMetadata($payload->metadata);
        }

        if (key_exists('merchant_reference', $payload)) {
            $model->setMerchantReference($payload->merchant_reference);
        }

        if (key_exists('merchant_response_url', $payload)) {
            $model->setMerchantResponseUrl($payload->merchant_response_url);
        }

        if (key_exists('merchant_checkout_url', $payload)) {
            $model->setMerchantCheckoutUrl($payload->merchant_checkout_url);
        }

        if (key_exists('merchant_redirect_url', $payload)) {
            $model->setMerchantRedirectUrl($payload->merchant_redirect_url);
        }

        if (key_exists('available_finance_options', $payload)) {
            $model->setAvailableFinanceOptions($payload->available_finance_options);
        }

        if (key_exists('merchant_channel_id', $payload)) {
            $model->setMerchantChannelId($payload->merchant_channel_id);
        }

        if (key_exists('finalisation_required', $payload)) {
            $model->setFinalisationRequired($payload->finalisation_required);
        }

        if (key_exists('form_data', $payload)) {
            $model->setFormData($payload->form_data);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/applications/{application}",
     *     tags={"Application"},
     *     description="Updates a application",
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
     *         description="The application",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Application",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_application.json"
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
     * @throws ContainerException
     * @throws GuzzleException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\Exceptions\ApplicationApiException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_application')->data;

        $model = new Application();
        $model->setId($id);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model, false);

        $model = $this->mapPayloadToModel($model, $payload);

        /** @var JsonFuse $jsonFuse */
        $jsonFuse = $this->container->get('Proxy.JsonFuse');

        /**
         * TODO:
         * Move to service instead of route?
         */

        if (key_exists('applicants', $payload)) {
            $applicants = $jsonFuse->fuse($model->getApplicants(), $payload->applicants);
            $model->setApplicants($applicants);
        }

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');

        if (key_exists('lender_fee_reported_date', $payload)) {
            $model->setLenderFeeReportedDate(new \DateTime($payload->lender_fee_reported_date));
            if (key_exists('lender_fee', $payload)) {
                $model->setLenderFee($payload->lender_fee);
            } else {
                /** @var LenderFeeService $lenderFeeService */
                $lenderFeeService = $this->container->get('Service.LenderFee');
                $model->setLenderFee($lenderFeeService->calculateLenderFee($model));
                $model->setCommission($lenderFeeService->calculateCommission($model));
                $model->setPartnerCommission($lenderFeeService->calculatePartnerCommission($model));
            }
        }

        $model = $service->update($model);

        /** @var ApplicationSchema $schema */
        $schema = new ApplicationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
