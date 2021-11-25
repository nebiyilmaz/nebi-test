<?php

namespace Divido\Routing\Refund;

use Divido\ResponseSchemas\RefundSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Refund\Refund;
use Divido\Services\Refund\RefundService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PatchRoutes
 *
 * Generic PUT routes (non business logic).
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param Refund $model
     * @param $payload
     * @return Refund
     */
    private function mapPayloadToModel(Refund $model, $payload)
    {
        if (key_exists('status', $payload)) {
            $model->setStatus($payload->status);
        }

        if (key_exists('reference', $payload)) {
            $model->setReference($payload->reference);
        }

        if (key_exists('comment', $payload)) {
            $model->setComment($payload->comment);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/refunds/{refund}",
     *     tags={"Refund"},
     *     description="Updates a refund",
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
     *         name="refund",
     *         in="path",
     *         required=true,
     *         description="The refund",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Refund",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_refund.json"
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

        $payload = $this->validate($request, 'patch_refund');

        $model = new Refund();
        $model->setId($id);

        /** @var RefundService $service */
        $service = $this->container->get('Service.Refund');
        $model = $service->getOne($model);

        $model = $this->mapPayloadToModel($model, $payload->data);

        $model = $service->update($model);

        /** @var RefundSchema $schema */
        $schema = new RefundSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
