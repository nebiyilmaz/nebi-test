<?php

namespace Divido\Routing\Activation;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\ActivationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Activation\Activation;
use Divido\Services\Activation\ActivationService;
use Interop\Container\Exception\ContainerException;
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
     * @param Activation $model
     * @param $payload
     * @return Activation
     */
    private function mapPayloadToModel(Activation $model, $payload)
    {
        if (key_exists('status', $payload)) {
            $model->setStatus($payload->status);
        }

        if (key_exists('reference', $payload)) {
            $model->setReference($payload->reference);
        }

        if (key_exists('delivery_method', $payload)) {
            $model->setDeliveryMethod($payload->delivery_method);
        }

        if (key_exists('tracking_number', $payload)) {
            $model->setTrackingNumber($payload->tracking_number);
        }

        if (key_exists('comment', $payload)) {
            $model->setComment($payload->comment);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/activations/{activation}",
     *     tags={"Activation"},
     *     description="Updates a activation",
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
     *         name="activation",
     *         in="path",
     *         required=true,
     *         description="The activation",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Activation",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_activation.json"
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
     * @throws PayloadPropertyMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws ContainerException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_activation');

        $model = new Activation();
        $model->setId($id);

        /** @var ActivationService $service */
        $service = $this->container->get('Service.Activation');
        $model = $service->getOne($model);
        $model = $this->mapPayloadToModel($model, $payload->data);
        $model = $service->update($model);

        /** @var ActivationSchema $schema */
        $schema = new ActivationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
