<?php

namespace Divido\Routing\LenderCall;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\LenderCommunicationApiSdk\LenderCommunicationApiSdkException;
use Divido\Routing\AbstractRoute;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Submission\Submission;
use Interop\Container\Exception\ContainerException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class DeleteRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class DeleteRoutes extends AbstractRoute
{
    /**
     * @OA\Delete(
     *     path="/call/{applicationSubmissionId}/{callName}",
     *     tags={"Lender Call"},
     *     description="Deletes a lender call",
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
     *         description="Ok"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws ApplicationSubmissionErrorException
     * @throws UpstreamServiceBadResponseException
     * @throws LenderCommunicationApiSdkException
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

        $data = $service->customCall($applicationSubmission, $callName, "DELETE", $params);

        return $this->json(['data' => $data], $response);
    }
}
