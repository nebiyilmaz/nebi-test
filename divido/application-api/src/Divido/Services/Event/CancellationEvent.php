<?php

namespace Divido\Services\Event;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\IncorrectApplicationCancellationStatusException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;

/**
 * Class CancellationEvent
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class CancellationEvent
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var CancellationService $cancellationService */
    private $cancellationService;

    /** @var ApplicationObjectBuilder $applicationObjectBuilder */
    private $applicationObjectBuilder;

    /** @var SubmissionObjectBuilder $submissionObjectBuilder */
    private $submissionObjectBuilder;

    /** @var LenderCommunicationApiSdk $lenderCommunicationApiSdk */
    private $lenderCommunicationApiSdk;

    /**
     * FormConfigurationService constructor.
     * @param ApplicationService $applicationService
     * @param CancellationService $cancellationService
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, CancellationService $cancellationService, ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder, LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->cancellationService = $cancellationService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param $id
     * @return Cancellation
     * @throws IncorrectApplicationCancellationStatusException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function event($id): Cancellation
    {
        $cancellation = (new Cancellation())->setId($id);
        $cancellation = $this->cancellationService->getOne($cancellation);

        $application = (new Application())->setId($cancellation->getApplicationId());
        $application = $this->applicationService->getOne($application);

        if (!in_array($cancellation->getStatus(), ['REQUESTED', 'ERROR'])) {
            throw new IncorrectApplicationCancellationStatusException($cancellation->getStatus());
        }

        try {
            $result = $this->send($application, $cancellation);

            $cancellation->setStatus($result->status)
                ->setAmount($result->amount)
                ->setReference($result->reference)
                ->setComment($result->comment);
        } catch (\Exception $e) {
            $cancellation->setStatus('ERROR');
        }

        $this->cancellationService->update($cancellation);

        $application = $this->applicationService->getOne($application, false);

        if ($application->getCancelableAmount() == 0 && $application->getActivatedAmountTotal() == 0) {
            $newStatus = "CANCELLED";
            $cancellations = $this->cancellationService->getAll($application);
            foreach ($cancellations as $cancellation) {
                if (in_array($cancellation->getStatus(), ['CANCELLED', 'AWAITING-CANCELLATION'])) {
                    if ($cancellation->getStatus() == 'AWAITING-CANCELLATION') {
                        $newStatus = $cancellation->getStatus();
                    }
                }
            }
            $this->applicationService->createApplicationStatusRequest($application, $newStatus);
        }

        return $this->cancellationService->getOne($cancellation);
    }

    /**
     * @param Cancellation $cancellation
     * @return object
     */
    private function getCancellationObject(Cancellation $cancellation): object
    {
        return (object) [
            'id' => $cancellation->getId(),
            'status' => $cancellation->getStatus(),
            'amount' => $cancellation->getAmount(),
            'product_data' => $cancellation->getProductData(),
            'reference' => $cancellation->getReference(),
            'comment' => $cancellation->getComment(),
            'created_at' => $cancellation->getCreatedAt()->format("c"),
            'updated_at' => $cancellation->getUpdatedAt()->format("c")
        ];
    }

    /**
     * @param Application $application
     * @param Cancellation $cancellation
     * @return object
     * @throws ApplicationSubmissionErrorException
     */
    private function send(Application $application, Cancellation $cancellation): object
    {
        $result = $this->lenderCommunicationApiSdk->cancellation(
            $this->applicationObjectBuilder->getObject($application),
            $this->submissionObjectBuilder->getSubmission($application),
            $this->getCancellationObject($cancellation)
        );

        $data = $result->data;

        return $data;
    }
}
