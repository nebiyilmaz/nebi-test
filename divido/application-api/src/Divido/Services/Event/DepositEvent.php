<?php

namespace Divido\Services\Event;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Deposit\Deposit;
use Divido\Services\Deposit\DepositService;

/**
 * Class DepositEvent
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class DepositEvent
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var DepositService $depositService */
    private $depositService;

    /** @var ApplicationObjectBuilder $applicationObjectBuilder */
    private $applicationObjectBuilder;

    /** @var SubmissionObjectBuilder $submissionObjectBuilder */
    private $submissionObjectBuilder;

    /** @var LenderCommunicationApiSdk $lenderCommunicationApiSdk */
    private $lenderCommunicationApiSdk;

    /**
     * FormConfigurationService constructor.
     * @param ApplicationService $applicationService
     * @param DepositService $depositService
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, DepositService $depositService, ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder, LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->depositService = $depositService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param $id
     * @return Deposit
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function event($id): Deposit
    {
        $deposit = (new Deposit())->setId($id);

        $deposit = $this->depositService->getOne($deposit);

        $application = (new Application())->setId($deposit->getApplicationId());
        $application = $this->applicationService->getOne($application);

        if ($this->depositService->getUnpaidDepositAmount($application) <= 0) {
            $this->applicationService->createApplicationStatusRequest($application, 'DEPOSIT-PAID');
            $application->setDepositStatus('PAID');
            $this->applicationService->update($application);
        }

        return $this->depositService->getOne($deposit, false);
    }

    /**
     * @param Deposit $deposit
     * @return array
     */
    private function getDepositObject(Deposit $deposit)
    {
        return [
            'id' => $deposit->getId(),
            'status' => $deposit->getStatus(),
            'amount' => $deposit->getAmount(),
            'type' => $deposit->getType(),
            'reference' => $deposit->getReference(),
            'data' => $deposit->getDataRaw(),
            'product_data' => $deposit->getProductData(),
            'merchant_comment' => $deposit->getMerchantComment(),
            'merchant_reference' => $deposit->getMerchantReference(),
            'created_at' => $deposit->getCreatedAt()->format("c"),
            'updated_at' => $deposit->getUpdatedAt()->format("c")
        ];
    }

    /**
     * @param Application $application
     * @param Deposit $deposit
     * @return object
     * @throws ApplicationSubmissionErrorException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\LenderCommunicationApiSdk\LenderCommunicationApiSdkException
     */
    private function send(Application $application, Deposit $deposit): object
    {
        /**
         * TODO:
         * This would allow us to send a request to lender-communication-api when a deposit happens
         * not sure we need it, lets evaluate for the Omni Capital integration
         */
        $result = $this->lenderCommunicationApiSdk->deposit(
            $this->applicationObjectBuilder->getObject($application),
            $this->submissionObjectBuilder->getSubmission($application),
            $this->getDepositObject($deposit)
        );

        $data = $result->data;

        return $data;
    }
}
