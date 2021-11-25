<?php

namespace Divido\Routing\Cancellation;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\LogStreamer\Logger;
use Divido\ResponseSchemas\CancellationSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;
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
     * @return Cancellation
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Cancellation();
        $model->setStatus($payload->status)
            ->setAmount($payload->amount)
            ->setProductData($payload->product_data)
            ->setReference($payload->reference)
            ->setComment($payload->comment ?? '');

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/cancellations",
     *     tags={"Cancellation"},
     *     description="Creates a new cancellation",
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
     *         request="Cancellation",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_cancellation.json"
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
        try {
            $payload = $this->validate($request, 'post_cancellation');

            $applicationId = $request->getAttribute('applicationId');

            $model = $this->mapPayloadToModel($payload->data);
            $model->setApplicationId($applicationId);

            /** @var CancellationService $service */
            $service = $this->container->get('Service.Cancellation');
            $model = $service->create($model);

            $schema = new CancellationSchema();

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
                'Creating cancellation',
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
