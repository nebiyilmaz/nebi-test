<?php

namespace Divido\Routing\Deposit;

use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\DepositSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Deposit\Deposit;
use Divido\Services\Deposit\DepositService;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PatchRoutes
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param Deposit $model
     * @param $payload
     * @return Deposit
     */
    private function mapPayloadToModel(Deposit $model, $payload)
    {
        if (property_exists($payload, 'status')) {
            $model->setStatus($payload->status);
        }

        if (property_exists($payload, 'reference')) {
            $model->setReference($payload->reference);
        }

        if (property_exists($payload, 'amount')) {
            $model->setAmount($payload->amount);
        }

        if (property_exists($payload, 'product_data')) {
            $model->setProductData($payload->product_data);
        }

        if (property_exists($payload, 'merchant_comment')) {
            $model->setMerchantComment($payload->merchant_comment);
        }

        if (property_exists($payload, 'type')) {
            $model->setType($payload->type);
        }

        if (property_exists($payload, 'data_raw')) {
            $model->setDataRaw($payload->data_raw);
        }

        if (property_exists($payload, 'merchant_reference')) {
            $model->setMerchantReference($payload->merchant_reference);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/deposits/{deposit}",
     *     tags={"Deposit"},
     *     description="Updates a deposit",
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
     *         name="deposit",
     *         in="path",
     *         required=true,
     *         description="The deposit",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Deposit",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_deposit.json"
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
     * @throws PayloadPropertyMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws ContainerException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_deposit');

        $model = new Deposit();
        $model->setId($id);

        /** @var DepositService $service */
        $service = $this->container->get('Service.Deposit');
        $model = $service->getOne($model);
        $model = $this->mapPayloadToModel($model, $payload->data);
        $model = $service->update($model);

        /** @var DepositSchema $schema */
        $schema = new DepositSchema();

        return $this->json(['data' => $schema->getData($model)], $response);
    }
}
