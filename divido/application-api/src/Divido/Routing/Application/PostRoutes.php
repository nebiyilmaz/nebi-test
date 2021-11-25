<?php

namespace Divido\Routing\Application;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\LogStreamer\Logger;
use Divido\MerchantApi\MerchantApiClientException;
use Divido\ResponseSchemas\ApplicationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use GuzzleHttp\Exception\GuzzleException;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Throwable;

/**
 * Class PostRoutes
 *
 * Generic POST routes (non business logic).
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @param $payload
     * @return Application
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Application();
        $model->setCountryCode($payload->country_code)
            ->setCurrencyCode($payload->currency_code)
            ->setLanguageCode($payload->language_code)
            ->setMerchantId($payload->merchant_id)
            ->setMerchantChannelId($payload->merchant_channel_id)
            ->setMerchantFinanceOptionId($payload->merchant_finance_option_id)
            ->setMerchantApiKeyId($payload->merchant_api_key_id)
            ->setMerchantUserId($payload->merchant_user_id)
            ->setFinalisationRequired($payload->finalisation_required)
            ->setPurchasePrice($payload->purchase_price)
            ->setDepositPercentage($payload->deposit_percentage)
            ->setDepositAmount($payload->deposit_amount)
            ->setDepositStatus($payload->deposit_status)
            ->setFormData($payload->form_data)
            ->setApplicants($payload->applicants)
            ->setProductData($payload->product_data)
            ->setMetadata($payload->metadata ?? (object) [])
            ->setMerchantReference($payload->merchant_reference ?? null)
            ->setMerchantResponseUrl($payload->merchant_response_url ?? null)
            ->setMerchantCheckoutUrl($payload->merchant_checkout_url ?? null)
            ->setMerchantRedirectUrl($payload->merchant_redirect_url ?? null)
            ->setAvailableFinanceOptions($payload->available_finance_options ?? []);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications",
     *     tags={"Application"},
     *     description="Creates a new application",
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
     *     @OA\RequestBody(
     *         request="Application",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_application.json"
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
     * @throws ContainerException
     * @throws GuzzleException
     * @throws MerchantApiClientException
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\ApplicationInputInvalidException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\WaterfallApiSdk\WaterfallApiSdkException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_application');

            $model = $this->mapPayloadToModel($payload->data);

            /** @var ApplicationService $service */
            $service = $this->container->get('Service.Application');
            $model = $service->create($model);

            $schema = new ApplicationSchema();

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
                'Creating application',
                [
                    'status_code' => $code ?? StatusCode::HTTP_OK,
                    'request' => $payload ?? '',
                    'response' => $data ?? '',
                    'error_message' => $message ?? '',
                ]
            );
        }
    }
}
