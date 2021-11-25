<?php

namespace Divido\Routing\Signatory;

use Divido\LogStreamer\Logger;
use Divido\ResponseSchemas\SignatorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Signatory\Signatory;
use Divido\Services\Signatory\SignatoryService;
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
     * @return Signatory
     * @throws \Exception
     */
    private function mapPayloadToModel($payload)
    {
        $model = new Signatory();
        $model->setFirstName($payload->first_name)
            ->setLastName($payload->last_name)
            ->setEmailAddress($payload->email_address)
            ->setTitle($payload->title)
            ->setDateOfBirth(new \DateTime($payload->date_of_birth))
            ->setLenderReference($payload->lender_reference)
            ->setHostedSigning($payload->hosted_signing)
            ->setDataRaw($payload->data_raw);

        return $model;
    }

    /**
     * @OA\Post(
     *     path="/applications/{application}/signatories",
     *     tags={"Signatory"},
     *     description="Creates a new signatory",
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
     *         request="Signatory",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_signatory.json"
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
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function create(/** @scrutinizer ignore-unused */ Request $request, Response $response)
    {
        try {
            $payload = $this->validate($request, 'post_signatory');

            $applicationId = $request->getAttribute('applicationId');

            $model = $this->mapPayloadToModel($payload->data);
            $model->setApplicationId($applicationId);

            /** @var SignatoryService $service */
            $service = $this->container->get('Service.Signatory');
            $model = $service->create($model);

            $schema = new SignatorySchema();

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
                'Creating signatory',
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
