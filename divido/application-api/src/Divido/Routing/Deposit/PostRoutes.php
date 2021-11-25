<?php

namespace Divido\Routing\Deposit;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\DepositSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Deposit\Deposit;
use Divido\Services\Deposit\DepositService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PostRoutes
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @param $payload
     * @return Deposit
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Deposit();
        $model
            ->setStatus($payload->status)
            ->setReference($payload->reference ?? '')
            ->setAmount($payload->amount)
            ->setProductData($payload->product_data)
            ->setMerchantComment($payload->merchant_comment ?? '')
            ->setType($payload->type ?? '')
            ->setDataRaw($payload->data_raw ?? (object) [])
            ->setMerchantReference($payload->merchant_reference);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/deposits",
     *     tags={"Deposit"},
     *     description="Creates a new deposit",
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
     *         request="Deposit",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_deposit.json"
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
     * @throws \Divido\ApiExceptions\ApplicationInputInvalidException
     * @throws \Divido\ApiExceptions\IncorrectApplicationStatusException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $payload = $this->validate($request, 'post_deposit');

        $applicationId = $request->getAttribute('applicationId');

        /** @var Deposit $model */
        $model = $this->mapPayloadToModel($payload->data);
        $model->setApplicationId($applicationId);

        /** @var DepositService $service */
        $service = $this->container->get('Service.Deposit');
        $model = $service->create($model);

        /** @var DepositSchema $schema */
        $schema = new DepositSchema();

        return $this->json(['data'=>$schema->getData($model)], $response);
    }
}
