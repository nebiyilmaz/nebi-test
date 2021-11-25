<?php

namespace Divido\Routing\Activation;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\ActivationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Activation\Activation;
use Divido\Services\Activation\ActivationService;
use Divido\Services\Application\Application;
use Interop\Container\Exception\ContainerException;
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
     *     path="/activations/{activation}",
     *     tags={"Activation"},
     *     description="Gets an activation",
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
     *         description="The activation id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="An activation"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws UnauthorizedException
     * @throws ResourceNotFoundException
     * @throws ContainerException
     */
    public function getOne(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $model = new Activation();
        $model->setId($id);

        /** @var ActivationService $service */
        $service = $this->container->get('Service.Activation');
        $model = $service->getOne($model);

        /** @var ActivationSchema $schema */
        $schema = new ActivationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/activations",
     *     tags={"Activation"},
     *     description="Gets all activations for an application",
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
     *         description="An array of activations"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function getAll(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $applicationId = $request->getAttribute('applicationId');

        /** @var ActivationService $service */
        $service = $this->container->get('Service.Activation');
        $models = $service->getAll((new Application())->setId($applicationId));

        $data = [];

        /** @var ActivationSchema $schema */
        $schema = new ActivationSchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }
}
