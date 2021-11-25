<?php

namespace Divido\Services\Event;

use Divido\ApiExceptions\IncorrectApplicationRefundStatusException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Refund\Refund;
use Divido\Services\Refund\RefundService;

/**
 * Class RefundEvent
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class RefundEvent
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var RefundService $refundService */
    private $refundService;

    /** @var ApplicationObjectBuilder $applicationObjectBuilder */
    private $applicationObjectBuilder;

    /** @var SubmissionObjectBuilder $submissionObjectBuilder */
    private $submissionObjectBuilder;

    /** @var LenderCommunicationApiSdk $lenderCommunicationApiSdk */
    private $lenderCommunicationApiSdk;

    /**
     * FormConfigurationService constructor.
     * @param ApplicationService $applicationService
     * @param RefundService $refundService
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, RefundService $refundService, ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder, LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->refundService = $refundService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param $id
     * @return Refund
     * @throws IncorrectApplicationRefundStatusException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function event($id): Refund
    {
        $refund = (new Refund())->setId($id);
        $refund = $this->refundService->getOne($refund);

        $application = (new Application())->setId($refund->getApplicationId());
        $application = $this->applicationService->getOne($application);

        if (!in_array($refund->getStatus(), ['REQUESTED', 'ERROR'])) {
            throw new IncorrectApplicationRefundStatusException($refund->getStatus());
        }

        try {
            $result = $this->send($application, $refund);

            $refund->setStatus($result->status)
                ->setAmount($result->amount)
                ->setReference($result->reference)
                ->setComment($result->comment);
        } catch (\Exception $e) {
            $refund->setStatus('ERROR');
        }

        $this->refundService->update($refund);

        $application = $this->applicationService->getOne($application, false);

        if ($application->getCancelableAmount() == 0 && $application->getActivatableAmount() == 0 && $application->getRefundableAmount() == 0) {
            $this->applicationService->createApplicationStatusRequest($application, 'REFUNDED');
        }

        return $this->refundService->getOne($refund);
    }

    /**
     * @param Refund $refund
     * @return object
     */
    private function getRefundObject(Refund $refund): object
    {
        return (object) [
            'id' => $refund->getId(),
            'status' => $refund->getStatus(),
            'amount' => $refund->getAmount(),
            'product_data' => $refund->getProductData(),
            'reference' => $refund->getReference(),
            'comment' => $refund->getComment(),
            'created_at' => $refund->getCreatedAt()->format("c"),
            'updated_at' => $refund->getUpdatedAt()->format("c")
        ];
    }

    /**
     * @param Application $application
     * @param Refund $refund
     * @return object
     * @throws \Divido\ApiExceptions\ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\LenderCommunicationApiSdk\LenderCommunicationApiSdkException
     */
    private function send(Application $application, Refund $refund): object
    {
        $result = $this->lenderCommunicationApiSdk->refund(
            $this->applicationObjectBuilder->getObject($application),
            $this->submissionObjectBuilder->getSubmission($application),
            $this->getRefundObject($refund)
        );

        $data = $result->data;

        return $data;
    }
}
