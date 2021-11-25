<?php

namespace Divido\Services\Activation;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationActivationNotPossibleException;
use Divido\ApiExceptions\ApplicationInputInvalidException;
use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\Proxies\Platform;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Ramsey\Uuid\Uuid;

/**
 * Class ActivationService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ActivationService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var EventService */
    private $eventService;

    /** @var ActivationDatabaseInterface */
    private $activationDatabaseInterface;

    /** @var Platform */
    private $platformProxy;

    /**
     * MerchantPortalService constructor.
     *
     * @param ApplicationService $applicationService
     * @param EventService $eventService
     * @param ActivationDatabaseInterface $activationDatabaseInterface
     * @param Platform $platformProxy
     */
    function __construct(
        ApplicationService $applicationService,
        EventService $eventService,
        ActivationDatabaseInterface $activationDatabaseInterface,
        Platform $platformProxy
    ) {
        $this->applicationService = $applicationService;
        $this->eventService = $eventService;
        $this->activationDatabaseInterface = $activationDatabaseInterface;
        $this->platformProxy = $platformProxy;
    }

    /**
     * @param Activation $model
     *
     * @return Activation
     * @throws ApplicationInputInvalidException
     * @throws IncorrectApplicationStatusException
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws ApplicationActivationNotPossibleException
     */
    public function create(Activation $model): Activation
    {
        $applicationModel = $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        if (!in_array($applicationModel->getStatus(), ['READY', 'PARTIALLY-ACTIVATED'])) {
            throw new IncorrectApplicationStatusException($applicationModel->getStatus());
        }

        if (is_null($model->getAmount()) || $model->getAmount() <= 0) {
            $model->setAmount($applicationModel->getActivatableAmount());
        }

        if ($applicationModel->getActivatableAmount() < $model->getAmount()) {
            throw new ApplicationInputInvalidException('amount', $applicationModel->getActivatableAmount().' lower than ' . $model->getAmount(), 'Requested activation amount is too high');
        }

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->activationDatabaseInterface->createNewActivationFromModel($model);
        $model->setId($id);

        try {
            $this->eventService->supports($applicationModel)
                ? $this->eventService->newEvent('activation', (object) ['application_activation_id' => $model->getId()])
                : $this->platformProxy->trigger(Platform::ACTIVATE_APPLICATION, ['id' => $model->getId()]);
        } catch (AbstractException $exception) {
            throw new ApplicationActivationNotPossibleException($exception->getContext());
        }

        $model = $this->activationDatabaseInterface->getActivationFromModel($model, false);

        return $model;
    }

    /**
     * @param Activation $model
     * @return Activation
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function update(Activation $model)
    {
        $this->getOne($model);
        $this->activationDatabaseInterface->updateActivationFromModel($model);

        $model = $this->activationDatabaseInterface->getActivationFromModel($model, false);

        return $model;
    }

    /**
     * @param Activation $model
     * @param int $useReadReplica
     * @return Activation
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(Activation $model, $useReadReplica = 1): Activation
    {
        $model = $this->activationDatabaseInterface->getActivationFromModel($model, $useReadReplica);

        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $application
     * @return array
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getAll(Application $application)
    {
        $this->applicationService->getOne($application);

        return $this->activationDatabaseInterface->getAllActivations($application);
    }
}
