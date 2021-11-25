<?php

namespace Divido\Routing\AlternativeOffer;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\AlternativeOfferSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\AlternativeOffer\AlternativeOffer;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PatchRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param AlternativeOffer $model
     * @param $payload
     * @return AlternativeOffer
     */
    private function mapPayloadToModel(AlternativeOffer $model, $payload)
    {
        if (key_exists('data', $payload)) {
            $model->setData($payload->data);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/alternative-offers/{alternativeOffer}",
     *     tags={"Alternative Offer"},
     *     description="Updates an alternative offer",
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
     *         name="alternativeOffer",
     *         in="path",
     *         required=true,
     *         description="The alternative offer",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Alternative Offer",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_alternative_offer.json"
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
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_alternative_offer');

        $model = new AlternativeOffer();
        $model->setId($id);

        /** @var AlternativeOfferService $service */
        $service = $this->container->get('Service.AlternativeOffer');
        $model = $service->getOne($model);

        $model = $this->mapPayloadToModel($model, $payload->data);

        $model = $service->update($model);

        /** @var AlternativeOfferSchema $schema */
        $schema = new AlternativeOfferSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
