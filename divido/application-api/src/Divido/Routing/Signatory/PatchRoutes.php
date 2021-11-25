<?php

namespace Divido\Routing\Signatory;

use Divido\ResponseSchemas\SignatorySchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Signatory\Signatory;
use Divido\Services\Signatory\SignatoryService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class PatchRoutes
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class PatchRoutes extends AbstractRoute
{
    /**
     * @param Signatory $model
     * @param $payload
     * @return Signatory
     * @throws \Exception
     */
    private function mapPayloadToModel(Signatory $model, $payload)
    {
        if (key_exists('first_name', $payload)) {
            $model->setFirstName($payload->first_name);
        }

        if (key_exists('last_name', $payload)) {
            $model->setLastName($payload->last_name);
        }

        if (key_exists('email_address', $payload)) {
            $model->setEmailAddress($payload->email_address);
        }

        if (key_exists('title', $payload)) {
            $model->setTitle($payload->title);
        }

        if (key_exists('date_of_birth', $payload)) {
            $model->setDateOfBirth(new \DateTime($payload->date_of_birth));
        }

        if (key_exists('lender_reference', $payload)) {
            $model->setLenderReference($payload->lender_reference);
        }

        if (key_exists('hosted_signing', $payload)) {
            $model->setHostedSigning($payload->hosted_signing);
        }

        if (key_exists('data_raw', $payload)) {
            $model->setDataRaw($payload->data_raw);
        }

        return $model;
    }

    /**
     * @OA\Patch(
     *     path="/signatories/{signatory}",
     *     tags={"Signatory"},
     *     description="Updates a signatory",
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
     *         name="signatory",
     *         in="path",
     *         required=true,
     *         description="The signatory",
     *         @OA\Schema(
     *             type="string",
     *             example="001e31c4-a1d8-421c-a46e-6ffec02f5384"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Signatory",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_signatory.json"
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
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function patch(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $payload = $this->validate($request, 'patch_signatory');

        $model = new Signatory();
        $model->setId($id);

        /** @var SignatoryService $service */
        $service = $this->container->get('Service.Signatory');
        $model = $service->getOne($model);

        $model = $this->mapPayloadToModel($model, $payload->data);

        $model = $service->update($model);

        /** @var SignatorySchema $schema */
        $schema = new SignatorySchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }
}
