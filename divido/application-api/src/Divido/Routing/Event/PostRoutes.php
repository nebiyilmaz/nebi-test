<?php

namespace Divido\Routing\Event;

use DateTime;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\LogStreamer\Logger;
use Divido\Routing\AbstractRoute;
use Divido\Services\Event\EventDispatcherService;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Throwable;

/**
 * Class PostRoutes
 *
 * Generic POST routes (non business logic).
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @OA\Post(
     *     path="/event",
     *     tags={"Event"},
     *     description="Actions the event",
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
     *     @OA\RequestBody(
     *         request="Event",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_action.json"
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
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     */
    public function newEvent(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_action')->data;

            /** @var EventDispatcherService $service */
            $service = $this->container->get('Service.EventDispatcher');
            $service->dispatcher($payload->event, $payload->data);

            return $this->json(['data' => ['executed_at' => new DateTime()]], $response);
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
                'Dispatching event',
                [
                    'status_code' => $code ?? StatusCode::HTTP_OK,
                    'request' => $payload ?? '',
                    'error_message' => $message ?? '',
                ]
            );
        }
    }
}
