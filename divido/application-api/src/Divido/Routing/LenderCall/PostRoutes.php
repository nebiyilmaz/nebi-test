<?php

namespace Divido\Routing\LenderCall;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use function GuzzleHttp\Psr7\stream_for;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PostRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PostRoutes extends AbstractRoute
{
    /**
     * @OA\Post(
     *     path="/call/{applicationSubmissionId}/{callName}",
     *     tags={"Lender Call"},
     *     description="Creates a new lender call",
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
     *         name="applicationSubmissionId",
     *         in="path",
     *         required=true,
     *         description="The application submission id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="callName",
     *         in="path",
     *         required=true,
     *         description="The call name",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Lender Call",
     *         required=true
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
     * @throws ApplicationSubmissionErrorException
     * @throws ContainerException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function call(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $callName = $request->getAttribute('callName');
        $params = $request->getParams();
        $payload = json_decode($request->getBody()->getContents()) ?? (object) [];

        $applicationSubmissionId = $request->getAttribute('applicationSubmissionId');
        $applicationSubmission = (new Submission())->setId($applicationSubmissionId);

        /** @var LenderCallService $service */
        $service = $this->container->get('Service.LenderCall');

        $callResponse = $service->customCall($applicationSubmission, $callName, "POST", $params, $payload);

        if (key_exists('type', $callResponse) && $callResponse->type == 'json') {
            return $this->json(['data' => json_decode($callResponse->data)], $response);
        } else if (key_exists('type', $callResponse) && $callResponse->type == 'html') {
            return $response->withBody(stream_for($callResponse->data));
        }

        return $this->json(['data' => $callResponse], $response);
    }

    /**
     * @OA\Post(
     *     path="/notification/{applicationSubmissionId}",
     *     tags={"Lender Call"},
     *     description="Notifies the lender",
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
     *         name="applicationSubmissionId",
     *         in="path",
     *         required=true,
     *         description="The application submission id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Lender Call",
     *         required=true
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
     * @throws ApplicationSubmissionErrorException
     * @throws ContainerException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function notification(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $params = $request->getParams();
        $payload = json_decode($request->getBody()->getContents()) ?? (object) [];

        $applicationSubmissionId = $request->getAttribute('applicationSubmissionId');
        $applicationSubmission = (new Submission())->setId($applicationSubmissionId);

        /** @var LenderCallService $service */
        $service = $this->container->get('Service.LenderCall');

        $notificationResponse = $service->notification($applicationSubmission, "POST", $params, $payload);

        if (!empty($notificationResponse->type) && $notificationResponse->type == 'json') {
            return $this->json(['data' => json_decode($notificationResponse->data)], $response);
        } else if (!empty($notificationResponse->type) && $notificationResponse->type == 'xml') {
            return $response->withHeader('Content_Type', 'application/xml')
                ->withBody(stream_for($notificationResponse->data));
        } else if (!empty($notificationResponse->type) && $notificationResponse->type == 'html') {
            return $response->withBody(stream_for($notificationResponse->data));
        }

        return $this->json(['data' => null], $response);
    }

    /**
     * @OA\Post(
     *     path="/submit/{token}",
     *     tags={"Lender Call"},
     *     description="Submits to the lender",
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
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="token",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Lender Call",
     *         required=true
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
     * @throws ApplicationSubmissionErrorException
     * @throws ContainerException
     * @throws PayloadPropertyMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws UpstreamServiceBadResponseException
     */
    public function submit(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        $payload = $this->validate($request, 'post_lender_call_submit')->data;

        /** @var LenderCallService $service */
        $service = $this->container->get('Service.LenderCall');
        $service->submit($token, $payload);

        /** @var FormConfigurationService $formConfigurationService */
        $formConfigurationService = $this->container->get('Service.FormConfiguration');

        $data = $formConfigurationService->render((new Application())->setToken($token), false);

        return $this->json([
            'meta' => [
                'application' => $data->application,
            ],
            'data' => $data->formConfiguration
        ], $response);

    }
}
