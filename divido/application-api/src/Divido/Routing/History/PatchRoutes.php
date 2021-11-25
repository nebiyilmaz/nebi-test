<?php

namespace Divido\Routing\History;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\HistorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\History\History;
use Divido\Services\History\HistoryService;
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
     * @param History $model
     * @param $payload
     * @return History
     */
    private function mapPayloadToModel(History $model, $payload)
    {
        if (key_exists('internal', $payload)) {
            $model->setInternal($payload->internal);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/histories/{history}",
     *     tags={"History"},
     *     description="Updates a history",
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
     *         name="history",
     *         in="path",
     *         required=true,
     *         description="The history",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="History",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_history.json"
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
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_history');

        $model = new History();
        $model->setId($id);

        /** @var HistoryService $service */
        $service = $this->container->get('Service.ApplicationHistory');
        $model = $service->getOne($model);
        $model = $this->mapPayloadToModel($model, $payload->data);
        $model = $service->update($model);

        /** @var HistorySchema $schema */
        $schema = new HistorySchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
