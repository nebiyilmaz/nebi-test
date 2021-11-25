<?php

namespace Divido\Services\Signatory;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Ramsey\Uuid\Uuid;

/**
 * Class SignatoryService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class SignatoryService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var SignatoryDatabaseInterface */
    private $signatoryDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param SignatoryDatabaseInterface $signatoryDatabaseInterface
     */
    function __construct(ApplicationService $applicationService, SignatoryDatabaseInterface $signatoryDatabaseInterface)
    {
        $this->applicationService = $applicationService;
        $this->signatoryDatabaseInterface = $signatoryDatabaseInterface;
    }

    /**
     * @param Signatory $model
     * @return Signatory
     * @throws \Exception
     */
    public function create(Signatory $model): Signatory
    {
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->signatoryDatabaseInterface->createNewSignatoryFromModel($model);
        $model->setId($id);

        $model = $this->signatoryDatabaseInterface->getSignatoryFromModel($model, false);

        return $model;
    }

    /**
     * @param Signatory $model
     * @return Signatory
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function update(Signatory $model)
    {
        $this->getOne($model);
        $this->signatoryDatabaseInterface->updateSignatoryFromModel($model);

        $model = $this->signatoryDatabaseInterface->getSignatoryFromModel($model, false);

        return $model;
    }

    /**
     * @param Signatory $model
     * @return Signatory
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(Signatory $model): Signatory
    {
        $model = $this->signatoryDatabaseInterface->getSignatoryFromModel($model);
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $application
     * @return array
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getAll(Application $application)
    {
        $this->applicationService->getOne($application);

        return $this->signatoryDatabaseInterface->getAllSignatories($application);
    }
}
