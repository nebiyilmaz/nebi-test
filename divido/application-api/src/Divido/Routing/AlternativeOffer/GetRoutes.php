<?php

namespace Divido\Routing\AlternativeOffer;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\AlternativeOfferSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\AlternativeOffer\AlternativeOffer;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Services\Application\Application;
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
     *     path="/alternative-offers/{alternative-offer}",
     *     tags={"Alternative Offer"},
     *     description="Gets an alternative offer",
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
     *         name="alternative-offer",
     *         in="path",
     *         required=true,
     *         description="The alternative offer id",
     *         @OA\Schema(
     *             type="string",
     *             example="0a9dcc30-f0f8-4e73-ab55-c2f37a480a4a"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="An alternative offer"
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

        $model = new AlternativeOffer();
        $model->setId($id);

        /** @var AlternativeOfferService $service */
        $service = $this->container->get('Service.AlternativeOffer');
        $model = $service->getOne($model);

        /** @var AlternativeOfferSchema $schema */
        $schema = new AlternativeOfferSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/alternative-offers",
     *     tags={"Alternative Offer"},
     *     description="Gets all alternative offers for an application",
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
     *         description="An array of alternative offers"
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

        /** @var AlternativeOfferService $service */
        $service = $this->container->get('Service.AlternativeOffer');
        $models = $service->getAll((new Application())->setId($applicationId));

        $data = [];

        /** @var AlternativeOfferSchema $schema */
        $schema = new AlternativeOfferSchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }
}
