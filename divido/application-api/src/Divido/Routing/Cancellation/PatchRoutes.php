<?php

namespace Divido\Routing\Cancellation;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\CancellationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;
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
     * @param Cancellation $model
     * @param $payload
     * @return Cancellation
     */
    private function mapPayloadToModel(Cancellation $model, $payload)
    {
        if (key_exists('status', $payload)) {
            $model->setStatus($payload->status);
        }

        if (key_exists('comment', $payload)) {
            $model->setComment($payload->comment);
        }

        if (key_exists('reference', $payload)) {
            $model->setReference($payload->reference);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/cancellations/{cancellation}",
     *     tags={"Cancellation"},
     *     description="Updates a cancellation",
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
     *         name="cancellation",
     *         in="path",
     *         required=true,
     *         description="The cancellation",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Cancellation",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_cancellation.json"
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
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_cancellation');

        $model = new Cancellation();
        $model->setId($id);

        /** @var CancellationService $service */
        $service = $this->container->get('Service.Cancellation');

        $model = $service->getOne($model);
        $model = $this->mapPayloadToModel($model, $payload->data);
        $model = $service->update($model);

        /** @var CancellationSchema $schema */
        $schema = new CancellationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
