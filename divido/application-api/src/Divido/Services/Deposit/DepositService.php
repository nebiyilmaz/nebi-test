<?php

namespace Divido\Services\Deposit;

use Divido\ApiExceptions\ApplicationDepositAmountInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Exception;
use Ramsey\Uuid\Uuid;

/**
 * Class DepositService
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class DepositService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var EventService */
    private $eventService;

    /** @var DepositDatabaseInterface */
    private $depositDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param EventService $eventService
     * @param DepositDatabaseInterface $depositDatabaseInterface
     */
    function __construct(
        ApplicationService $applicationService,
        EventService $eventService,
        DepositDatabaseInterface $depositDatabaseInterface
    ) {
        $this->applicationService = $applicationService;
        $this->eventService = $eventService;
        $this->depositDatabaseInterface = $depositDatabaseInterface;
    }

    /**
     * @param Deposit $model
     * @return Deposit
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function create(Deposit $model): Deposit
    {
        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        if ($model->getAmount() <= 0) {
            throw new ApplicationDepositAmountInvalidException($model->getApplicationId(), $model->getAmount());
        }

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->depositDatabaseInterface->createNewDepositFromModel($model);
        $model->setId($id);

        $this->eventService->newEvent('deposit', (object) ['application_deposit_id' => $model->getId()]);

        $model = $this->depositDatabaseInterface->getDepositFromModel($model, false);

        return $model;
    }

    /**
     * @param Deposit $model
     * @return Deposit
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function update(Deposit $model)
    {
        $this->getOne($model);
        $this->depositDatabaseInterface->updateDepositFromModel($model);

        $model = $this->depositDatabaseInterface->getDepositFromModel($model, false);

        return $model;
    }

    /**
     * @param Deposit $model
     * @param int $useReadReplica
     * @return Deposit
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function getOne(Deposit $model, $useReadReplica = 1): Deposit
    {
        $model = $this->depositDatabaseInterface->getDepositFromModel($model, $useReadReplica);

        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $application
     * @return array
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function getAll(Application $application)
    {
        $this->applicationService->getOne($application);

        return $this->depositDatabaseInterface->getAllDeposits($application);
    }

    /**
     * @param Application $application
     * @return int
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function getUnpaidDepositAmount(Application $application)
    {
        $unpaidDepositAmount = $application->getDepositAmount();

        $deposits = $this->getAll($application);

        foreach($deposits as $deposit) {
            $unpaidDepositAmount -= $deposit->getAmount();
        }

        return $unpaidDepositAmount;
    }
}
