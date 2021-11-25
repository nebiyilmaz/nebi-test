<?php

namespace Divido\Services\History;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;

/**
 * Class ApplicationHistoryService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class HistoryService
{
    /** @var ApplicationService */
    private $applicationService;

    /** @var HistoryDatabaseInterface */
    private $applicationHistoryDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param HistoryDatabaseInterface $applicationHistoryDatabaseInterface
     */
    function __construct(ApplicationService $applicationService, HistoryDatabaseInterface $applicationHistoryDatabaseInterface)
    {
        $this->applicationService = $applicationService;
        $this->applicationHistoryDatabaseInterface = $applicationHistoryDatabaseInterface;
    }

    /**
     * @param History $model
     * @return History
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function create(History $model): History
    {
        $applicationModel = $this->applicationService->getOne((new Application())->setId($model->getApplicationId()), false);

        $this->applicationHistoryDatabaseInterface->createNewHistoryFromModel($model);
        $model = $this->applicationHistoryDatabaseInterface->getHistoryFromModel($model, false);

        if (!empty($model->getStatus()) && $applicationModel->getStatus() != $model->getStatus()) {
            $this->applicationService->updateStatus($applicationModel, $model);
        }

        $model = $this->applicationHistoryDatabaseInterface->getHistoryFromModel($model, false);

        return $model;
    }

    /**
     * @param History $model
     * @return History
     * @throws ResourceNotFoundException
     */
    public function update(History $model)
    {
        $this->applicationHistoryDatabaseInterface->updateHistoryFromModel($model);

        $model = $this->applicationHistoryDatabaseInterface->getHistoryFromModel($model, false);

        return $model;
    }

    /**
     * @param History $model
     * @return History
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(History $model): History
    {
        $model = $this->applicationHistoryDatabaseInterface->getHistoryFromModel($model);
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $applicationModel
     * @return array
     * @throws \Exception
     */
    public function getAll(Application $applicationModel)
    {
        $applicationModel = $this->applicationService->getOne($applicationModel);

        return $this->applicationHistoryDatabaseInterface->getAllHistories($applicationModel);
    }
}
