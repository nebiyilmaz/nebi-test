<?php

namespace Divido\Services\FormConfiguration;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;

/**
 * Class FormConfigurationService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class FormConfigurationService
{
    /** @var ApplicationService $applicationService */
    private $applicationService;

    /** @var SubmissionService $submissionService */
    private $submissionService;

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
     * @param ApplicationObjectBuilder $applicationObjectBuilder
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @param LenderCommunicationApiSdk $lenderCommunicationApiSdk
     */
    function __construct(ApplicationService $applicationService, SubmissionService $submissionService, ApplicationObjectBuilder $applicationObjectBuilder, SubmissionObjectBuilder $submissionObjectBuilder, LenderCommunicationApiSdk $lenderCommunicationApiSdk)
    {
        $this->applicationService = $applicationService;
        $this->submissionService = $submissionService;
        $this->applicationObjectBuilder = $applicationObjectBuilder;
        $this->submissionObjectBuilder = $submissionObjectBuilder;
        $this->lenderCommunicationApiSdk = $lenderCommunicationApiSdk;
    }

    /**
     * @param Application $application
     * @param bool $useReadReplica
     * @return array
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function render(Application $application, $useReadReplica = true)
    {
        $application = $this->applicationService->getOne($application, $useReadReplica);

        $applicationObject = $this->applicationObjectBuilder->getObject($application, $useReadReplica);
        $submissions = $this->submissionObjectBuilder->getAllSubmissions($application, $useReadReplica);

        $client = (!empty($_SERVER['HTTP_X_DIVIDO_CLIENT_FORWARDED_IP_ADDRESS'])) ? [
            'ip_address' => $_SERVER['HTTP_X_DIVIDO_CLIENT_FORWARDED_IP_ADDRESS']
        ] : [];

        $result = $this->lenderCommunicationApiSdk->renderFormConfiguration($applicationObject, $submissions, ['client' => $client]);
        $data = $result->data;

        if (isset($data->submission)) {
            $submission = $this->submissionService->getOne((new Submission())->setId($data->submission->id));
            $submission->setId($data->submission->id)
                ->setStatus($data->submission->status)
                ->setLenderStatus($data->submission->lender_status)
                ->setLenderReference($data->submission->lender_reference)
                ->setLenderLoanReference($data->submission->lender_loan_reference)
                ->setLenderData($data->submission->lender_data);

            $this->submissionService->update($submission);
        }

        $application = (object)[
            'country' => $applicationObject->country,
            'language' => $applicationObject->language,
            'currency' => $applicationObject->currency,
            'id' => $applicationObject->id,
            'merchant' => (object)[
                'id' => $applicationObject->merchant->id,
                'css' => $applicationObject->merchant->settings['layout']['css'],
                'styling' => $applicationObject->merchant->settings['layout']['styling']
            ]
        ];

        return [
            'application' => $application,
            'formConfiguration' => (!empty($data->form_configuration)) ? $data->form_configuration : null
        ];

    }
}
