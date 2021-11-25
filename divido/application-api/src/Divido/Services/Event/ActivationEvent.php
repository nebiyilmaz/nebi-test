<?php

namespace Divido\Services\Event;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\IncorrectApplicationActivationStatusException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Activation\Activation;
use Divido\Services\Activation\ActivationService;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;

/**
 * Class ActivationEvent
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ActivationEvent
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var ActivationService $activationService */
    private $activationService;

    /** @var ApplicationObjectBuilder $applicationObjectBuilder */
    private $applicationObjectBuilder;

    /** @var SubmissionObjectBuilder $submissionObjectBuilder */
    private $submissionObjectBuilder;

    /** @var LenderCommunicationApiSdk $lenderCommunicationApiSdk */
    private $lenderCommunicationApiSdk;

    /**
     * FormConfigurationService constructor.
     * @param ApplicationService $applicationService
     * @param ActivationService $activationService
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, ActivationService $activationService, ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder, LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->activationService = $activationService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param $id
     * @return Activation
     * @throws IncorrectApplicationActivationStatusException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function event($id): Activation
    {
        $activation = (new Activation())->setId($id);
        $activation = $this->activationService->getOne($activation);

        $application = (new Application())->setId($activation->getApplicationId());
        $application = $this->applicationService->getOne($application);

        if (!in_array($activation->getStatus(), ['REQUESTED', 'ERROR'])) {
            throw new IncorrectApplicationActivationStatusException($activation->getStatus());
        }

        try {
            $result = $this->send($application, $activation);

            $activation->setStatus($result->status)
                ->setAmount($result->amount)
                ->setReference($result->reference)
                ->setComment($result->comment);
        } catch (\Exception $e) {
            $activation->setStatus('ERROR');
        }

        $this->activationService->update($activation);

        $application = $this->applicationService->getOne($application, false);

        if ($application->getActivatableAmount() == 0 && $application->getCancelableAmount() == 0) {
            $activations = $this->activationService->getAll($application);
            $activated = true;
            foreach ($activations as $activation) {
                if (in_array($activation->getStatus(), ['ACTIVATED', 'AWAITING-ACTIVATION']) && $activation->getStatus() === 'AWAITING-ACTIVATION') {
                    $activated = false;
                }
            }
            $this->applicationService->createApplicationStatusRequest($application, ($activated) ? 'ACTIVATED':'AWAITING-ACTIVATION');
        } else if ($application->getActivatedAmount() > 0) {
            $this->applicationService->createApplicationStatusRequest($application, 'PARTIALLY-ACTIVATED');
        }

        return $this->activationService->getOne($activation, false);
    }

    /**
     * @param Activation $activation
     * @return object
     */
    private function getActivationObject(Activation $activation): object
    {
        return (object) [
            'id' => $activation->getId(),
            'status' => $activation->getStatus(),
            'amount' => $activation->getAmount(),
            'product_data' => $activation->getProductData(),
            'reference' => $activation->getReference(),
            'comment' => $activation->getComment(),
            'delivery_method' => $activation->getDeliveryMethod(),
            'tracking_number' => $activation->getTrackingNumber(),
            'created_at' => $activation->getCreatedAt()->format("c"),
            'updated_at' => $activation->getUpdatedAt()->format("c")
        ];
    }

    /**
     * @param Application $application
     * @param Activation $activation
     * @return object
     * @throws ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\LenderCommunicationApiSdk\LenderCommunicationApiSdkException
     */
    private function send(Application $application, Activation $activation): object
    {
        $result = $this->lenderCommunicationApiSdk->activation(
            $this->applicationObjectBuilder->getObject($application),
            $this->submissionObjectBuilder->getSubmission($application),
            $this->getActivationObject($activation)
        );

        $data = $result->data;

        return $data;
    }
}
