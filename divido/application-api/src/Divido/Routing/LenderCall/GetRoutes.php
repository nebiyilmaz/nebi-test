<?php

namespace Divido\Routing\LenderCall;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Routing\AbstractRoute;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use function GuzzleHttp\Psr7\stream_for;
use Interop\Container\Exception\ContainerException;
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
     *     path="/call/{applicationSubmissionId}/{callName}",
     *     tags={"Lender Call"},
     *     description="Gets a lender call",
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
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A lender call"
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
    public function call(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $callName = $request->getAttribute('callName');
        $params = $request->getParams();

        $applicationSubmissionId = $request->getAttribute('applicationSubmissionId');
        $applicationSubmission = (new Submission())->setId($applicationSubmissionId);

        /** @var LenderCallService $service */
        $service = $this->container->get('Service.LenderCall');

        $callResponse = $service->customCall($applicationSubmission, $callName, "GET", $params);

        if (key_exists('type', $callResponse) && $callResponse->type == 'json') {
            return $this->json(['data' => json_decode($callResponse->data)], $response);
        } else if (key_exists('type', $callResponse) && $callResponse->type == 'html') {
            return $response->withBody(stream_for($callResponse->data));
        }

        return $this->json(['data' => null], $response);
    }

    /**
     * @OA\Get(
     *     path="/query/{applicationSubmissionId}",
     *     tags={"Lender Call"},
     *     description="Queries the lender communication",
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
     *     @OA\Response(
     *         response=200,
     *         description="OK"
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
    public function query(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        $applicationSubmissionId = $request->getAttribute('applicationSubmissionId');
        $applicationSubmission = (new Submission())->setId($applicationSubmissionId);

        /** @var LenderCallService $service */
        $service = $this->container->get('Service.LenderCall');

        $data = $service->query($applicationSubmission);

        return $this->json(['data' => $data], $response);
    }
}
