<?php

namespace Divido\Routing\Submission;

use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\ResponseSchemas\SubmissionSchema;
use Divido\Routing\AbstractRoute;
use Divido\Services\Application\Application;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
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
     *     path="/submissions/{submission}",
     *     tags={"Submission"},
     *     description="Gets a submission",
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
     *         name="submission",
     *         in="path",
     *         required=true,
     *         description="The submission id",
     *         @OA\Schema(
     *             type="string",
     *             example="0004f028-a485-11e9-800d-0242ac110009"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A submission"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     */
    public function getOne(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        $model = new Submission();
        $model->setId($id);

        /** @var SubmissionService $service */
        $service = $this->container->get('Service.Submission');
        $model = $service->getOne($model);

        if (in_array($request->getParam('extra', false), ['true', '1'])) {
            return $this->getOneWithExtra($request, $response, $model);
        }

        /** @var SubmissionSchema $schema */
        $schema = new SubmissionSchema();

        return $this->json(['data' => $schema->getData($model)], $response);

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Submission $submission
     * @return Response
     * @throws ContainerException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     */
    public function getOneWithExtra(Request $request, Response $response, Submission $submission)
    {
        /** @var SubmissionObjectBuilder $submissionObjectBuilder */
        $submissionObjectBuilder = $this->container->get('Helper.SubmissionObjectBuilder');
        $applicationObject = $submissionObjectBuilder->getSubmission((new Application())->setId($submission->getApplicationId()), $submission);

        return $this->json(['data' => $applicationObject], $response);
    }

    /**
     * @OA\Get(
     *     path="/applications/{application}/submissions",
     *     tags={"Submission"},
     *     description="Gets all submissions for an application",
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
     *     @OA\Response(
     *         response=200,
     *         description="An array of submissions"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws ContainerException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     */
    public function getAll(/** @scrutinizer ignore-unused */
        Request $request, Response $response)
    {
        $applicationId = $request->getAttribute('applicationId');

        $applicationModel = (new Application())->setId($applicationId);

        if (in_array($request->getParam('extra', false), ['true', '1'])) {
            return $this->getAllWithExtra($request, $response, $applicationModel);
        }

        /** @var SubmissionService $service */
        $service = $this->container->get('Service.Submission');
        $models = $service->getAll($applicationModel);

        $data = [];

        /** @var SubmissionSchema $schema */
        $schema = new SubmissionSchema();
        foreach ($models as $model) {
            $data[] = $schema->getData($model);
        }

        return $this->json(['data' => $data], $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Application $application
     * @return Response
     * @throws ContainerException
     * @throws \Divido\ApiExceptions\ApplicationSubmissionErrorException
     */
    public function getAllWithExtra(Request $request, Response $response, Application $application)
    {
        /** @var SubmissionObjectBuilder $submissionObjectBuilder */
        $submissionObjectBuilder = $this->container->get('Helper.SubmissionObjectBuilder');
        $applicationObject = $submissionObjectBuilder->getAllSubmissions($application);

        return $this->json(['data' => $applicationObject], $response);
    }
}
