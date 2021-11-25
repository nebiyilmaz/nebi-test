<?php

namespace Divido\Services\Event;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\IncorrectApplicationStatusException;
use Divido\ApiExceptions\PayloadPropertiesMissingOrInvalidException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\LenderCommunicationApiSdk\LenderCommunicationApiSdkException;
use Divido\Services\Activation\Activation;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Refund\Refund;

/**
 * Class EventDispatcherService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class EventDispatcherService
{
    /** @var CancellationEvent $cancellationEvent */
    private $cancellationEvent;

    /** @var ActivationEvent $activationEvent */
    private $activationEvent;

    /** @var RefundEvent $refundEvent */
    private $refundEvent;

    /** @var DepositEvent $depositEvent */
    private $depositEvent;

    /**
     * MerchantPortalService constructor.
     * @param CancellationEvent $cancellationEvent
     * @param ActivationEvent $activationEvent
     * @param RefundEvent $refundEvent
     * @param DepositEvent $depositEvent
     */
    function __construct(CancellationEvent $cancellationEvent, ActivationEvent $activationEvent, RefundEvent $refundEvent, DepositEvent $depositEvent)
    {
        $this->cancellationEvent = $cancellationEvent;
        $this->activationEvent = $activationEvent;
        $this->refundEvent = $refundEvent;
        $this->depositEvent = $depositEvent;
    }

    /**
     * @param $event
     * @param $data
     * @return void
     * @throws ResourceNotFoundException
     */
    public function dispatcher($event, $data)
    {
        if (in_array($event, ['cancellation', 'activation', 'refund', 'deposit'])) {
            return $this->$event($data);
        } else {
            throw new ResourceNotFoundException($event, 'event', $event);
        }
    }

    /**
     * @param $data
     * @return Cancellation
     * @throws PayloadPropertiesMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws ApplicationSubmissionErrorException
     * @throws IncorrectApplicationStatusException
     * @throws UpstreamServiceBadResponseException
     * @throws LenderCommunicationApiSdkException
     * @throws \Divido\ApiExceptions\IncorrectApplicationCancellationStatusException
     */
    public function cancellation($data)
    {
        if (empty($data->application_cancellation_id)) {
            throw new PayloadPropertiesMissingOrInvalidException('application_cancellation_id');
        }

        return $this->cancellationEvent->event($data->application_cancellation_id);
    }

    /**
     * @param $data
     * @return Activation
     * @throws PayloadPropertiesMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\IncorrectApplicationActivationStatusException
     */
    public function activation($data)
    {
        if (empty($data->application_activation_id)) {
            throw new PayloadPropertiesMissingOrInvalidException('application_activation_id');
        }

        return $this->activationEvent->event($data->application_activation_id);
    }

    /**
     * @param $data
     * @return Refund
     * @throws PayloadPropertiesMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \Divido\ApiExceptions\IncorrectApplicationRefundStatusException
     */
    public function refund($data)
    {
        if (empty($data->application_refund_id)) {
            throw new PayloadPropertiesMissingOrInvalidException('application_refund_id');
        }

        return $this->refundEvent->event($data->application_refund_id);
    }

    /**
     * @param $data
     * @return \Divido\Services\Deposit\Deposit
     * @throws PayloadPropertiesMissingOrInvalidException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function deposit($data)
    {
        if (empty($data->application_deposit_id)) {
            throw new PayloadPropertiesMissingOrInvalidException('application_deposit_id');
        }

        return $this->depositEvent->event($data->application_deposit_id);
    }
}
