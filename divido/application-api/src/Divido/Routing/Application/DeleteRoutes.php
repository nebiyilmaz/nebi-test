<?php

namespace Divido\Routing\Application;

use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class DeleteRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class DeleteRoutes extends AbstractRoute
{
    /**
     * @OA\Delete(
     *     path="/applications/{application}",
     *     tags={"Application"},
     *     description="Deletes a application",
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
     *         description="The application being deleted",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function delete(/** @scrutinizer ignore-unused */ Request $request, /** @scrutinizer ignore-unused */ Response $response)
    {
        $id = $request->getAttribute('id');

        $model = new Application();
        $model->setId($id);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $service->delete($model);

        return $response->withStatus(200);

    }
}
