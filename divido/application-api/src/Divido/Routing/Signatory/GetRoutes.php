<?php

namespace Divido\Routing\Signatory;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\SignatorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Signatory\Signatory;
use Divido\Services\Signatory\SignatoryService;
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
     *     path="/signatories/{signatory}",
     *     tags={"Signatory"},
     *     description="Gets a signatory",
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
     *         name="signatory",
     *         in="path",
     *         required=true,
     *         description="The signatory id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A signatory"
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

        $model = new Signatory();
        $model->setId($id);

        /** @var SignatoryService $service */
        $service = $this->container->get('Service.Signatory');
        $model = $service->getOne($model);

        /** @var SignatorySchema $schema */
        $schema = new SignatorySchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/signatories",
     *     tags={"Signatory"},
     *     description="Gets all signatories for an application",
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
     *         description="An array of signatories"
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
    public function getAll(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $applicationId = $request->getAttribute('applicationId');

        /** @var SignatoryService $service */
        $service = $this->container->get('Service.Signatory');
        $models = $service->getAll((new Application())->setId($applicationId));

        $data = [];

        /** @var SignatorySchema $schema */
        $schema = new SignatorySchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }
}
