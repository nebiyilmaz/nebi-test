<?php

namespace Divido\Services\Refund;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationRefundNotPossibleException;
use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Proxies\Platform;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Ramsey\Uuid\Uuid;

/**
 * Class RefundService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class RefundService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var EventService */
    private $eventService;

    /** @var RefundDatabaseInterface */
    private $refundDatabaseInterface;

    /** @var Platform */
    private $platformProxy;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param EventService $eventService
     * @param RefundDatabaseInterface $refundDatabaseInterface
     */
    function __construct(
        ApplicationService $applicationService,
        EventService $eventService,
        RefundDatabaseInterface $refundDatabaseInterface,
        Platform $platformProxy
    ) {
        $this->applicationService = $applicationService;
        $this->eventService = $eventService;
        $this->refundDatabaseInterface = $refundDatabaseInterface;
        $this->platformProxy = $platformProxy;
    }

    /**
     * @param Refund $model
     *
     * @return Refund
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws ApplicationRefundNotPossibleException
     */
    public function create(Refund $model): Refund
    {
        $applicationModel = $this->applicationService->getOne((new Application())->setId($model->getApplicationId()));

        if (!in_array($applicationModel->getStatus(), [
            'AWAITING-ACTIVATION',
            'PARTIALLY-ACTIVATED',
            'ACTIVATED',
            'COMPLETED',
        ])) {
            throw new IncorrectApplicationStatusException($applicationModel->getStatus());
        }

        if (is_null($model->getAmount()) || $model->getAmount() <= 0) {
            $model->setAmount($applicationModel->getRefundableAmount());
        }

        if ($applicationModel->getRefundableAmount() < $model->getAmount()) {
            throw new \Exception('Requested refund amount is too high');
            /** TODO
             * Create a new exception
             */
        }

        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->refundDatabaseInterface->createNewRefundFromModel($model);
        $model->setId($id);

        $model = $this->refundDatabaseInterface->getRefundFromModel($model, false);

        try {
            $this->eventService->supports($applicationModel)
                ? $this->eventService->newEvent('refund', (object) ['application_refund_id' => $model->getId()])
                : $this->platformProxy->trigger(Platform::REFUND_APPLICATION, ['id' => $model->getId()]);
        } catch (AbstractException $exception) {
            throw new ApplicationRefundNotPossibleException($exception->getContext());
        }

        return $model;
    }

    /**
     * @param Refund $model
     * @return Refund
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function update(Refund $model): Refund
    {
        $this->getOne($model);
        $this->refundDatabaseInterface->updateRefundFromModel($model);

        $model = $this->refundDatabaseInterface->getRefundFromModel($model, false);

        return $model;
    }

    /**
     * @param Refund $model
     * @return Refund
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(Refund $model): Refund
    {
        $model = $this->refundDatabaseInterface->getRefundFromModel($model);

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

        return $this->refundDatabaseInterface->getAllRefunds($application);
    }
}
