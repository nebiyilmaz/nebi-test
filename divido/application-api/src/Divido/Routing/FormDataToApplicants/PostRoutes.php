<?php

declare(strict_types=1);

namespace Divido\Routing\FormDataToApplicants;

use Divido\Helpers\MapFormDataToApplicants;
use Divido\Routing\AbstractRoute;
use Slim\Http\Request;
use Slim\Http\Response;

class PostRoutes extends AbstractRoute
{
    /**
     * @OA\Post(
     *     path="/form-data-to-applicants",
     *     tags={"Form Data To Applicants"},
     *     description="Transforms form data to applicants",
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
     *         request="Form Data To Applicants",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="./src/Divido/JsonSchemas/post_form_data_to_applicants.json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ok"
     *     )
     * )
     *
     */
    public function convert(Request $request, Response $response)
    {
        $formData = $this->validate($request, 'post_form_data_to_applicants');

        $applicants = (object) [
            'value' => [
                (object) [
                    'value' => null,
                ],
            ],
        ];

        $mapFormDataToApplicants = new MapFormDataToApplicants();

        $applicants = $mapFormDataToApplicants->getApplicants($formData, $applicants);

        return $this->json(json_encode($applicants), $response);
    }
}
