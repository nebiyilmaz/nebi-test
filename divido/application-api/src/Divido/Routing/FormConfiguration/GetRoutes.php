<?php

namespace Divido\Routing\FormConfiguration;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\TenantMissingOrInvalidException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Middleware\TenantMiddleware;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\Tenant\TenantService;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018, Divido
 */
class GetRoutes extends AbstractRoute
{
    /**
     * @OA\Get(
     *     path="/form-configuration/{token}",
     *     tags={"Form Configuration"},
     *     description="",
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
     *         description="The token id",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
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
     * @throws ContainerException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function render(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        /** @var FormConfigurationService $service */
        $service = $this->container->get('Service.FormConfiguration');

        $data = $service->render((new Application())->setToken($token), false);

        return $this->json([
            'data' => $data['formConfiguration'],
            'meta' => [
                'application' => $data['application']
            ]
        ], $response);
    }

    /**
     * @OA\Get(
     *     path="/form-configuration",
     *     tags={"Form Configuration"},
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
     *         description="Ok"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     * @throws TenantMissingOrInvalidException
     */
    public function index(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $env = $this->container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID);

        /** @var TenantService $platformEnvironmentService */
        $platformEnvironmentService = $this->container->get('Service.PlatformEnvironment');
        $platformEnvironment = $platformEnvironmentService->getOne($env);

        $settings = $platformEnvironment->getSettings();

        $data = null;

        if (!empty($settings['application_form']['default_page']['component'])) {
            $data = $settings['application_form']['default_page']['component'];

        }

        return $this->json(['data' => $data], $response);
    }
}
