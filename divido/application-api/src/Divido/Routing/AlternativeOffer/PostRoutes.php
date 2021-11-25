<?php

namespace Divido\Routing\AlternativeOffer;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\AlternativeOfferSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\AlternativeOffer\AlternativeOffer;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PostRoutes
 *
 * Generic POST routes (non business logic).
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @param $payload
     * @return AlternativeOffer
     */
    private function mapPayloadToModel($payload)
    {
        $model = new AlternativeOffer();
        $model->setLenderId($payload->lender_id)
            ->setData($payload->data);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/alternative-offers",
     *     tags={"Alternative Offer"},
     *     description="Creates a new alternative offer",
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
     *     @OA\RequestBody(
     *         request="Alternative Offer",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_alternative_offer.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ok"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $payload = $this->validate($request, 'post_alternative_offer');

        $applicationId = $request->getAttribute('applicationId');

        /** @var ApplicationService $applicationService */
        $applicationService = $this->container->get('Service.Application');
        $applicationModel = (new Application())->setId($applicationId);
        $applicationModel = $applicationService->getOne($applicationModel);

        /** @var AlternativeOffer $model */
        $model = $this->mapPayloadToModel($payload->data);
        $model->setApplicationId($applicationModel->getId());

        /** @var AlternativeOfferService $service */
        $service = $this->container->get('Service.AlternativeOffer');
        $model = $service->create($model);

        /** @var AlternativeOfferSchema $schema */
        $schema = new AlternativeOfferSchema();

        return $this->json(['data'=>$schema->getData($model)], $response);
    }
}
