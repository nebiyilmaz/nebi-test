<?php

namespace Divido\Routing\Application;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\Paginator\PaginatorHelper;
use Divido\ResponseSchemas\ApplicationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
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
     *     path="/applications/{application}",
     *     tags={"Application"},
     *     description="Gets an application",
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
     *         description="An application"
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

        $model = new Application();
        $model->setId($id);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model);

        if (in_array($request->getParam('extra', false), ['true', '1'])) {
            return $this->getOneWithExtra($request, $response, $model);
        }

        /** @var ApplicationSchema $schema */
        $schema = new ApplicationSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Application $application
     * @return Response
     * @throws ContainerException
     */
    public function getOneWithExtra(Request $request, Response $response, Application $application)
    {
        /** @var ApplicationObjectBuilder $applicationObjectBuilder */
        $applicationObjectBuilder = $this->container->get('Helper.ApplicationObjectBuilder');
        $applicationObject = $applicationObjectBuilder->getObject($application);

        return $this->json(['data' => $applicationObject], $response);
    }

    /**
     * @OA\Get(
     *     path="/applications",
     *     tags={"Application"},
     *     description="Gets all applications",
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
     *     @OA\Response(
     *         response=200,
     *         description="An array of applications"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     */
    public function getAll(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $paginator = new PaginatorHelper($request, 'created_at', 'desc',
            [
                'name',
                'branch_id',
                'token',
                'application_submission_id',
                'merchant_channel_id',
                'country_code',
                'currency_code',
                'language_code',
                'status',
                'merchant_id',
                'created_after',
                'updated_before',
                'updated_after',
                'deposit_status',
            ],
            ['name', 'status', 'merchant_id', 'created_at'],
            [
                'currency_code' => 'currency_id',
                'country_code' => 'country_id',
                'language_code' => 'language_id',
            ]
        );

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $models = $service->getAll($paginator);

        $data = [];

        /** @var ApplicationSchema $schema */
        $schema = new ApplicationSchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data, 'meta' => $paginator->getMeta()], $response);
    }
}
