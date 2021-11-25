<?php

namespace Divido\Routing\Applicant;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\ApplicationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
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
     *     path="/applicants/{token}",
     *     tags={"Applicant"},
     *     description="Gets an applicant",
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
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="The token of the application",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="An applicant (or array of applicants)"
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
    public function applicants(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        $model = new Application();
        $model->setToken($token);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model);

        /** @var ApplicationSchema $schema */
        return $this->json(['data' => $model->getApplicants()], $response);

    }

    /**
     * @OA\Get(
     *     path="/form-data/{token}",
     *     tags={"Form Data"},
     *     description="Gets form data from application",
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
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="The token of the application",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The form data for an application"
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
    public function formData(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        $model = new Application();
        $model->setToken($token);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model);

        /** @var ApplicationSchema $schema */
        return $this->json(['data' => $model->getFormData()], $response);

    }
}
