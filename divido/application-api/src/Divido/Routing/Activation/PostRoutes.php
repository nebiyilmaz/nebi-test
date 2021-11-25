<?php

namespace Divido\Routing\Activation;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\LogStreamer\Logger;
use Divido\ResponseSchemas\ActivationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Activation\Activation;
use Divido\Services\Activation\ActivationService;
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
     * @return Activation
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Activation();
        $model->setStatus($payload->status)
            ->setReference($payload->reference)
            ->setAmount($payload->amount)
            ->setProductData($payload->product_data)
            ->setDeliveryMethod($payload->delivery_method)
            ->setTrackingNumber($payload->tracking_number)
            ->setComment($payload->comment ?? '');

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/activations",
     *     tags={"Activation"},
     *     description="Creates a new activation",
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
     *         request="Activation",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_activation.json"
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
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\ApplicationInputInvalidException
     * @throws \Divido\ApiExceptions\IncorrectApplicationStatusException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_activation');

            $applicationId = $request->getAttribute('applicationId');

            $model = $this->mapPayloadToModel($payload->data);
            $model->setApplicationId($applicationId);

            /** @var ActivationService $service */
            $service = $this->container->get('Service.Activation');
            $model = $service->create($model);

            $schema = new ActivationSchema();

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
                'Creating activation',
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
