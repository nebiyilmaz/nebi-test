<?php

namespace Divido\Routing\Cancellation;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\CancellationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class GetRoutes extends AbstractRoute
{
    /**
     * @OA\Get(
     *     path="/cancellations/{cancellation}",
     *     tags={"Cancellation"},
     *     description="Gets a cancellation",
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
     *         description="The cancellation id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A cancellation"
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

        $model = new Cancellation();
        $model->setId($id);

        /** @var CancellationService $service */
        $service = $this->container->get('Service.Cancellation');
        $model = $service->getOne($model);

        /** @var CancellationSchema $schema */
        $schema = new CancellationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/cancellations",
     *     tags={"Cancellation"},
     *     description="Gets all cancellations for an application",
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
     *         description="An array of cancellations"
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

        /** @var CancellationService $service */
        $service = $this->container->get('Service.Cancellation');
        $models = $service->getAll((new Application())->setId($applicationId));

        $data = [];

        /** @var CancellationSchema $schema */
        $schema = new CancellationSchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }
}
