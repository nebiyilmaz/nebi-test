<?php

namespace Divido\Services\Submission;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Ramsey\Uuid\Uuid;

/**
 * Class SubmissionService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class SubmissionService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var SubmissionDatabaseInterface */
    private $submissionDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param SubmissionDatabaseInterface $submissionDatabaseInterface
     */
    function __construct(ApplicationService $applicationService, SubmissionDatabaseInterface $submissionDatabaseInterface)
    {
        $this->applicationService = $applicationService;
        $this->submissionDatabaseInterface = $submissionDatabaseInterface;
    }

    /**
     * @param Submission $model
     * @return Submission
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function create(Submission $model): Submission
    {
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->submissionDatabaseInterface->createNewSubmissionFromModel($model);
        $model->setId($id);

        $model = $this->submissionDatabaseInterface->getSubmissionFromModel($model, false);

        return $model;
    }

    /**
     * @param Submission $model
     * @return Submission
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function update(Submission $model): Submission
    {
        $this->getOne(clone($model), false);
        $this->submissionDatabaseInterface->updateSubmissionFromModel($model);

        $model = $this->submissionDatabaseInterface->getSubmissionFromModel($model, false);

        return $model;
    }

    /**
     * @param Submission $model
     * @param bool $useReadReplica
     * @return Submission
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(Submission $model, $useReadReplica = true): Submission
    {
        $model = $this->submissionDatabaseInterface->getSubmissionFromModel($model, $useReadReplica);
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $application
     * @param bool $useReadReplica
     * @return array
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getAll(Application $application, $useReadReplica = true)
    {
        $this->applicationService->getOne($application);

        return $this->submissionDatabaseInterface->getAllSubmissions($application, $useReadReplica);
    }
}
