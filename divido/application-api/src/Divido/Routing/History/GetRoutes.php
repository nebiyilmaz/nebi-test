<?php

namespace Divido\Routing\History;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\HistorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\History\History;
use Divido\Services\History\HistoryService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetRoutes
 *
 * Generic GET routes (non business logic).
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class GetRoutes extends AbstractRoute
{
    /**
     * @OA\Get(
     *     path="/histories/{history}",
     *     tags={"History"},
     *     description="Gets a history",
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
     *         description="The history id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A history"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getOne(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $model = (new History())
            ->setId($id);

        /** @var HistoryService $service */
        $service = $this->container->get('Service.ApplicationHistory');
        $model = $service->getOne($model);

        /** @var HistorySchema $schema */
        $schema = new HistorySchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/histories",
     *     tags={"History"},
     *     description="Gets all histories for an application",
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
     *     @OA\Response(
     *         response=200,
     *         description="An array of histories"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getAll(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $applicationId = $request->getAttribute('applicationId');

        /** @var HistoryService $service */
        $service = $this->container->get('Service.ApplicationHistory');
        $models = $service->getAll((new Application())->setId($applicationId));

        $data = [];

        /** @var HistorySchema $schema */
        $schema = new HistorySchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }
}
