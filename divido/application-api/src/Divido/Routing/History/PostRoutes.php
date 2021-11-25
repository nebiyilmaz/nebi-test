<?php

namespace Divido\Routing\History;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\ResponseSchemas\HistorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\History\History;
use Divido\Services\History\HistoryService;
use Slim\Http\Request;
use Slim\Http\Response;

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
     * @return History
     * @throws \Exception
     */
    private function mapPayloadToModel($payload)
    {
        $model = new History();
        $model->setStatus($payload->status ?? "")
            ->setUser($payload->user ?? "")
            ->setSubject($payload->subject ?? "")
            ->setText($payload->text ?? "")
            ->setInternal($payload->internal ?? false)
            ->setDate(new \DateTime($payload->date))
            ->setIpAddress($payload->ip_address);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/comments",
     *     tags={"History"},
     *     description="Creates a new comment",
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
     *         request="History Comment",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_application_comment.json"
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
    public function createComment(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $payload = $this->validate($request, 'post_application_comment');

        $applicationId = $request->getAttribute('applicationId');

        $model = $this->mapPayloadToModel($payload->data);
        $model->setType("comment")
            ->setApplicationId($applicationId);

        /** @var HistoryService $service */
        $service = $this->container->get('Service.ApplicationHistory');
        $model = $service->create($model);

        /** @var HistorySchema $schema */
        $schema = new HistorySchema();

        return $this->json(['data'=>$schema->getData($model)], $response);
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/statuses",
     *     tags={"History"},
     *     description="Creates a new status",
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
     *         request="History Status",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_status.json"
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
    public function createStatus(/** @scrutinizer ignore-unused */Request $request, Response $response)
    {
        $payload = $this->validate($request, 'post_status');

        $applicationId = $request->getAttribute('applicationId');

        $model = $this->mapPayloadToModel($payload->data);
        $model->setType("status")
            ->setApplicationId($applicationId);

        /** @var HistoryService $service */
        $service = $this->container->get('Service.ApplicationHistory');
        $model = $service->create($model);

        /** @var HistorySchema $schema */
        $schema = new HistorySchema();

        return $this->json(['data'=>$schema->getData($model)], $response);
    }
}
