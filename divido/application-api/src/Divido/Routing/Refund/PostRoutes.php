<?php

namespace Divido\Routing\Refund;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\LogStreamer\Logger;
use Divido\ResponseSchemas\RefundSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Refund\Refund;
use Divido\Services\Refund\RefundService;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Throwable;

/**
 * Class PostRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @param $payload
     * @return Refund
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Refund();
        $model->setStatus($payload->status)
            ->setAmount($payload->amount)
            ->setProductData($payload->product_data)
            ->setReference($payload->reference)
            ->setComment($payload->comment ?? '');

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/refunds",
     *     tags={"Refund"},
     *     description="Creates a new refund",
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
     *         request="Refund",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_refund.json"
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
     * @throws \Slim\Exception\NotFoundException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_refund');

            $applicationId = $request->getAttribute('applicationId');

            $model = $this->mapPayloadToModel($payload->data);
            $model->setApplicationId($applicationId);

            /** @var RefundService $service */
            $service = $this->container->get('Service.Refund');
            $model = $service->create($model);

            $schema = new RefundSchema();

            $data = $schema->getData($model);

            return $this->json(['data' => $data], $response);
        } catch (Throwable $e) {
            $level = 'error';
            $code = $e->getCode();
            $message = $e->getMessage();

            throw $e;
        } finally {
            /** @var Logger $logger */
            $logger = $this->container->get('Logger');

            $logger->log(
                $level ?? 'info',
                'Creating refund',
                [
                    'status_code' => $code ?? StatusCode::HTTP_OK,
                    'application_id' => $applicationId ?? '',
                    'request' => $payload ?? '',
                    'response' => $data ?? '',
                    'error_message' => $message ?? '',
                ]
            );
        }
    }
}
