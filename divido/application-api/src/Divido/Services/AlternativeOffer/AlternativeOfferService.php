<?php

namespace Divido\Services\AlternativeOffer;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Ramsey\Uuid\Uuid;

/**
 * Class AlternativeOfferService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class AlternativeOfferService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /**
     * @var AlternativeOfferDatabaseInterface
     */
    private $applicationAlternativeOfferDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param ApplicationService $applicationService
     * @param AlternativeOfferDatabaseInterface $applicationAlternativeOfferDatabaseInterface
     */
    function __construct(ApplicationService $applicationService, AlternativeOfferDatabaseInterface $applicationAlternativeOfferDatabaseInterface)
    {
        $this->applicationService = $applicationService;
        $this->applicationAlternativeOfferDatabaseInterface = $applicationAlternativeOfferDatabaseInterface;
    }

    /**
     * @param AlternativeOffer $model
     * @return AlternativeOffer
     * @throws \Exception
     */
    public function create(AlternativeOffer $model): AlternativeOffer
    {
        $id = Uuid::uuid4()->toString();
        $model->setId($id);

        $this->applicationAlternativeOfferDatabaseInterface->createNewAlternativeOfferFromModel($model);
        $model->setId($id);

        $model = $this->applicationAlternativeOfferDatabaseInterface->getAlternativeOfferFromModel($model, false);

        return $model;
    }

    /**
     * @param AlternativeOffer $model
     * @return AlternativeOffer
     * @throws ResourceNotFoundException
     */
    public function update(AlternativeOffer $model)
    {
        $this->applicationAlternativeOfferDatabaseInterface->updateAlternativeOfferFromModel($model);

        $model = $this->applicationAlternativeOfferDatabaseInterface->getAlternativeOfferFromModel($model, false);

        return $model;
    }

    /**
     * @param AlternativeOffer $model
     * @return AlternativeOffer
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     */
    public function getOne(AlternativeOffer $model): AlternativeOffer
    {
        $model = $this->applicationAlternativeOfferDatabaseInterface->getAlternativeOfferFromModel($model);

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

        return $this->applicationAlternativeOfferDatabaseInterface->getAllAlternativeOffers($application);
    }
}
