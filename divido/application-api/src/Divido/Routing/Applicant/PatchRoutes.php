<?php

namespace Divido\Routing\Applicant;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Exceptions\ApplicationApiException;
use Divido\Helpers\MapApplicantsToFormData;
use Divido\Helpers\MapFormDataToApplicants;
use Divido\Proxies\JsonFuse;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use GuzzleHttp\Exception\GuzzleException;
use Interop\Container\Exception\ContainerException;
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
     * @OA\Patch(
     *     path="/applicants/{token}",
     *     tags={"Applicant"},
     *     description="Updates an applicant",
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
     *         description="The token of the application",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Application",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_applicants.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="An applicant (or array of applicants)"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ApplicationApiException
     * @throws ContainerException
     * @throws GuzzleException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     */
    public function applicants(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        $payload = $this->validate($request, 'patch_applicants')->data;

        $model = new Application();
        $model->setToken($token);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model);

        /** @var JsonFuse $jsonFuse */
        $jsonFuse = $this->container->get('Proxy.JsonFuse');

        try {

            if (!empty($payload->value[0]->value) && !empty($_SERVER['HTTP_X_DIVIDO_CLIENT_FORWARDED_IP_ADDRESS'])) {
                $clientIpAddressesArray = explode(',', $_SERVER['HTTP_X_DIVIDO_CLIENT_FORWARDED_IP_ADDRESS']);

                if (empty($payload->value[0]->value->additional_fields)) {
                    $payload->value[0]->value->additional_fields = (object)['value'=>(object)[]];
                }
                if (empty($payload->value[0]->value->additional_fields->value->ip_address)) {
                    $payload->value[0]->value->additional_fields->value->ip_address = (object)['value'=>(object)[]];
                }
                $payload->value[0]->value->additional_fields->value->ip_address->value = trim($clientIpAddressesArray[0]);
            }

            $mapApplicantsToFormData = new MapApplicantsToFormData();
            $applicants = $jsonFuse->fuse($model->getApplicants(), $payload);
            $model->setFormData($mapApplicantsToFormData->getFormData($applicants));
            $model->setApplicants($applicants);
        } catch (ApplicationApiException $e) {
            throw $e;
        }

        $model = $service->update($model);

        return $this->json(['data' => $model->getApplicants()], $response);

    }

    /**
     * @OA\Patch(
     *     path="/form-data/{token}",
     *     tags={"Form Data"},
     *     description="Updates an applicant",
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
     *         description="The token of the application",
     *         @OA\Schema(
     *             type="string",
     *             example="34f2b12a3b8abb9d3fb45a8e089f8fba"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Application",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/patch_form_data.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The form data for an application"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ApplicationApiException
     * @throws ContainerException
     * @throws GuzzleException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\PayloadPropertyMissingOrInvalidException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     */
    public function formData(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $token = $request->getAttribute('token');

        $formData = $this->validate($request, 'patch_form_data')->data;

        if (key_exists('formData', $formData)) {
            $formData = $formData->formData;
        }

        $model = new Application();
        $model->setToken($token);

        /** @var ApplicationService $service */
        $service = $this->container->get('Service.Application');
        $model = $service->getOne($model);

        $formData = $this->mergeFormData($formData, $model->getFormData());

        /** @var JsonFuse $jsonFuse */
        $jsonFuse = $this->container->get('Proxy.JsonFuse');

        $formDataToApplicants = new MapFormDataToApplicants();

        $applicants = $formDataToApplicants->getApplicants($formData, $model->getApplicants());

        try {
            $applicants = $jsonFuse->fuse(null, $applicants);
            $model->setApplicants($applicants);
        } catch (ApplicationApiException $e) {

        }

        $model->setFormData($formData);

        $model = $service->update($model);

        return $this->json(['data' => $model->getFormData()], $response);

    }

    /**
     * @param $new
     * @param $old
     * @return mixed
     */
    private function mergeFormData($new, $old)
    {
        $newArr = json_decode(json_encode($old), 1);
        $oldArr = json_decode(json_encode($new), 1);

        $newArr = array_merge($newArr, $oldArr);

        return json_decode(json_encode($newArr), 0);
    }
}
