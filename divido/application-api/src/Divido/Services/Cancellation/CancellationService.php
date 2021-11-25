<?php

namespace Divido\Services\Cancellation;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationCancellationNotPossibleException;
use Divido\ApiExceptions\ApplicationInputInvalidException;
use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Proxies\Platform;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Ramsey\Uuid\Uuid;

/**
 * Class CancellationService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class CancellationService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var EventService */
    private $eventService;

    /** @var CancellationDatabaseInterface */
    private $cancellationDatabaseInterface;

    /** @var Platform */
    private $platformProxy;

    /**ö
     * MerchantPortalService constructor.
     *
     * @param ApplicationService $applicationService
     * @param EventService $eventService
     * @param CancellationDatabaseInterface $cancellationDatabaseInterface
     * @param Platform $platformProxy
     */
    function __construct(
        ApplicationService $applicationService,
        EventService $eventService,
        CancellationDatabaseInterface $cancellationDatabaseInterface,
        Platform $platformProxy
    ) {
        $this->applicationService = $applicationService;
        $this->eventService = $eventService;
        $this->cancellationDatabaseInterface = $cancellationDatabaseInterface;
        $this->platformProxy = $platformProxy;
    }

    /**
     * @param Cancellation $model
     *
     * @return Cancellation
     * @throws ApplicationInputInvalidException
     * @throws IncorrectApplicationStatusException
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws ApplicationCancellationNotPossibleException
     */
    public function create(Cancellation $model): Cancellation
    {
        $applicationModel = $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        if (!in_array($applicationModel->getStatus(), [
            'ACCEPTED',
            'ACTION-CUSTOMER',
            'ACTION-LENDER',
            'ACTION-RETAILER',
            'AWAITING-DECISION',
            'AWAITING-FINALIZATION',
            'DEPOSIT-PAID',
            'DRAFT',
            'ERROR',
            'FRAUD',
            'INFO-NEEDED',
            'PARTIALLY-ACTIVATED',
            'PENDING-APPLICATION',
            'PENDING-CUSTOMER',
            'PENDING-LENDER',
            'PENDING-MERCHANT',
            'PROPOSAL',
            'READY',
            'REFERRED',
            'SIGNED',
            'SUBMITTING',
            'UNSUBMITTED',
        ])) {
            throw new IncorrectApplicationStatusException($applicationModel->getStatus());
        }

        if (is_null($model->getAmount()) || $model->getAmount() <= 0) {
            $model->setAmount($applicationModel->getCancelableAmount());
        }

        if ($applicationModel->getCancelableAmount() < $model->getAmount()) {
            throw new ApplicationInputInvalidException('amount', $applicationModel->getCancelableAmount() . ' lower than ' . $model->getAmount(), 'Requested cancellation amount is too high');
        }

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->cancellationDatabaseInterface->createNewCancellationFromModel($model);
        $model->setId($id);

        try {
            $this->eventService->supports($applicationModel)
                ? $this->eventService->newEvent('cancellation', (object) ['application_cancellation_id' => $model->getId()])
                : $this->platformProxy->trigger(Platform::CANCEL_APPLICATION, ['id' => $model->getId()]);
        } catch (AbstractException $exception) {
            throw new ApplicationCancellationNotPossibleException($exception->getContext());
        }

        $model = $this->cancellationDatabaseInterface->getCancellationFromModel($model, false);

        return $model;
    }

    /**
     * @param Cancellation $model
     * @return Cancellation
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function update(Cancellation $model): Cancellation
    {
        $this->getOne($model);
        $this->cancellationDatabaseInterface->updateCancellationFromModel($model);

        $model = $this->cancellationDatabaseInterface->getCancellationFromModel($model, false);

        return $model;
    }

    /**
     * @param Cancellation $model
     * @return Cancellation
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(Cancellation $model): Cancellation
    {
        $model = $this->cancellationDatabaseInterface->getCancellationFromModel($model);

        $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        return $model;
    }

    /**
     * @param Application $application
     * @return array
     * @throws \Exception
     */
    public function getAll(Application $application)
    {
        $this->applicationService->getOne($application);

        return $this->cancellationDatabaseInterface->getAllCancellations($application);
    }
}
