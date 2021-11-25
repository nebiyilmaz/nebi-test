<?php

namespace Divido\Services\LenderCall;

use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Psr\Log\LoggerAwareTrait;

/**
 * Class LenderCallService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class LenderCallService
{
    use LoggerAwareTrait;

    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var SubmissionService $submissionService */
    private $submissionService;

    /** @var FormConfigurationService $formConfigurationService */
    private $formConfigurationService;

    /** @var ApplicationObjectBuilder $applicationObjectBuilder */
    private $applicationObjectBuilder;

    /** @var SubmissionObjectBuilder $submissionObjectBuilder */
    private $submissionObjectBuilder;

    /** @var LenderCommunicationApiSdk $lenderCommunicationApiSdk */
    private $lenderCommunicationApiSdk;

    /**
     * FormConfigurationService constructor.
     * @param ApplicationService $applicationService
     * @param SubmissionService $submissionService
     * @param FormConfigurationService $formConfigurationService
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, SubmissionService $submissionService, FormConfigurationService $formConfigurationService,
                         ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder,
                         LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->submissionService = $submissionService;
        $this->formConfigurationService = $formConfigurationService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param $token
     * @return bool
     * @throws ApplicationSubmissionErrorException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     * @throws UpstreamServiceBadResponseException
     */
    public function submit($token)
    {
        $application = (new Application())->setToken($token);
        $application = $this->applicationService->getOne($application, false);

        $this->applicationService->submit($application);

        return true;
    }

    /**
     * @param Submission $applicationSubmission
     * @param $callName
     * @param $method
     * @param array $params
     * @param null $payload
     * @return void
     * @throws ApplicationSubmissionErrorException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function customCall(Submission $applicationSubmission, $callName, $method, $params = [], $payload = null)
    {
        $applicationSubmission = $this->submissionService->getOne($applicationSubmission);
        $application = $this->applicationService->getOne((new Application())->setId($applicationSubmission->getApplicationId()));

        $customCall = (object)[
            'method' => $method,
            'call' => $callName,
            'params' => (object)$params
        ];

        if (in_array($method, ["POST", "PUT", "PATCH"])) {
            $customCall->payload = $payload;
        }

        $this->logger->debug('custom call', ['customCall' => $customCall]);

        $client = $payload->client ?? null;

        $response = $this->lenderCommunicationApiSdk->customCall(
            $customCall,
            $this->applicationObjectBuilder->getObject($application, false),
            $this->submissionObjectBuilder->getSubmission($application, $applicationSubmission),
            [
                'client' => $client
            ]
        );

        $this->logger->debug('updateSubmission', ['response' => $response]);
        $application = $this->updateSubmission($application, $applicationSubmission, $response);

        $response = (key_exists('response', $response)) ? $response->response : null;

        if ((empty($response->type) || !in_array($response->type, ['json', 'html', 'null']) || empty($response->data)) && in_array(strtoupper($method), ['POST', 'PATCH', 'PUT'])) {

            $formConfiguration = $this->formConfigurationService->render($application, false);
            $response = $formConfiguration['formConfiguration'];

        }

        return $response;
    }

    /**
     * @param Submission $applicationSubmission
     * @param $method
     * @param array $params
     * @param null $payload
     * @return array |null |null |null
     * @throws ApplicationSubmissionErrorException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function notification(Submission $applicationSubmission, $method, $params = [], $payload = null)
    {
        $applicationSubmission = $this->submissionService->getOne($applicationSubmission);
        $application = $this->applicationService->getOne((new Application())->setId($applicationSubmission->getApplicationId()));

        $applicationObject = $this->applicationObjectBuilder->getObject($application);
        $submission = $this->submissionObjectBuilder->getSubmission($application, $applicationSubmission);

        $origin = $payload->origin;
        $httpRequest = $origin->http_request;
        unset($origin->http_request);
        $notification = (object)[
            'origin' => $origin,
            'http_request' => $httpRequest,
            'data' => $payload->data
        ];

        $response = $this->lenderCommunicationApiSdk->notification(
            $notification,
            $applicationObject,
            $submission
        );

        $this->updateSubmission($application, $applicationSubmission, $response);

        return (key_exists('response', $response)) ? $response->response : null;

    }

    /**
     * @param Submission $applicationSubmission
     * @return null |null |null
     * @throws ApplicationSubmissionErrorException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function query(Submission $applicationSubmission)
    {
        $applicationSubmission = $this->submissionService->getOne($applicationSubmission);
        $application = $this->applicationService->getOne((new Application())->setId($applicationSubmission->getApplicationId()));

        $applicationObject = $this->applicationObjectBuilder->getObject($application);
        $submission = $this->submissionObjectBuilder->getSubmission($application, $applicationSubmission);

        $response = $this->lenderCommunicationApiSdk->query($applicationObject, $submission);

        $application = $this->updateSubmission($application, $applicationSubmission, $response);

        return [
            'id' => $application->getId(),
            'application_submission_id' => $applicationSubmission->getId(),
            'status' => $application->getStatus(),
            'next_check_interval' => $response->next_check_interval
        ];
    }

    /**
     * @param Application $application
     * @param Submission $oldSubmission
     * @param $response
     * @return Application
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function updateSubmission(Application $application, Submission $oldSubmission, $response)
    {
        # see https://divido.atlassian.net/browse/DIV-1658
        if (isset($response->update_submission) && $response->update_submission === false) {
            return $application;
        }

        $data = $response->data;

        $submission = (new Submission())->setId($data->id);
        $submission = $this->submissionService->getOne($submission, false);

        $submission->setStatus($data->status)
            ->setLenderStatus($data->lender_status)
            ->setLenderReference($data->lender_reference)
            ->setLenderLoanReference($data->lender_loan_reference)
            ->setLenderData($data->lender_data);

        $submission = $this->submissionService->update($submission);

        if ($oldSubmission->getStatus() !== $submission->getStatus()) {
            $this->applicationService->submissionStatusChangeListener($application, $submission);
            $application = $this->applicationService->getOne($application, false);
        }

        return $application;
    }
}
