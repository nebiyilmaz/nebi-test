<?php

namespace Divido\Services\Event;

use Divido\ApplicationApiSdk\Client as ApplicationApiSdk;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\Services\Application\Application;
use Divido\Services\Tenant\Tenant;
use Divido\Services\Tenant\TenantService;

/**
 * Class EventService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class EventService
{
    /** @var Tenant $tenant */
    private $tenant;

    /** @var ApplicationApiSdk $applicationApiSdk */
    private $applicationApiSdk;

    /** @var SubmissionObjectBuilder */
    private $submissionObjectBuilder;

    /**
     * @param TenantService $tenantService
     * @param ApplicationApiSdk $applicationApiSdk
     * @param SubmissionObjectBuilder $submissionObjectBuilder
     * @throws \Divido\ApiExceptions\TenantMissingOrInvalidException
     */
    public function __construct(
        TenantService $tenantService,
        ApplicationApiSdk $applicationApiSdk,
        SubmissionObjectBuilder $submissionObjectBuilder
    ) {
        $this->applicationApiSdk = $applicationApiSdk;
        $this->tenant = $tenantService->getOne();
        $this->submissionObjectBuilder = $submissionObjectBuilder;
    }

    /**
     * @param $type
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function newEvent($type, $data)
    {
        if (!in_array($type, ['cancellation', 'activation', 'refund', 'deposit'])) {
            throw new \Exception('Invalid event type. Must be cancellation, activation or refund.');
        }

        $payload = [
            'event' => $type,
            'data' => $data
        ];

        if (
            !empty($_SERVER['DIVIDO_APPLICATION_ENVIRONMENT']) && in_array($_SERVER['DIVIDO_APPLICATION_ENVIRONMENT'], ['development', 'staging', 'testing']) &&
            !empty($_SERVER['HTTP_X_DIVIDO_RETURN_PAYLOAD']) && $_SERVER['HTTP_X_DIVIDO_RETURN_PAYLOAD'] == 'true') {
            header("Content-Type: application/json");
            print json_encode(['data' => $payload]);
            exit;
        }

        $this->applicationApiSdk->createEvent($payload);

        return true;
    }

    /**
     * @param Application $application
     * @return bool
     */
    public function supports(Application $application): bool
    {
        $submission = $this->submissionObjectBuilder->getSubmission($application);

        return $submission->lender->settings['generic']->supports_v2 ?? false;
    }
}
