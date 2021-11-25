<?php

namespace Divido\Proxies;

use Aws\Sqs\SqsClient;
use Psr\Log\LoggerAwareTrait;

/**
 * Class LenderApplicationStatusWkr
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2020, Divido
 */
class LenderApplicationStatusWkrProxy
{
    use LoggerAwareTrait;

    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var string $tenantId
     */
    private $tenantId;

    /**
     * @var string $que
     */
    private $que;

    /**
     * Calculator constructor.
     * @param SqsClient $client
     * @param string $tenantId
     * @param string $que
     */
    public function __construct(SqsClient $client, string $tenantId, string $que)
    {
        $this->client = $client;
        $this->tenantId = $tenantId;
        $this->que = $que;
    }

    /**
     * @param string $applicationSubmissionId
     * @param string $status
     * @return mixed
     */
    public function statusChange(string $applicationSubmissionId, string $status)
    {
        $this->logger->info('add application update to sqs', [
            'que' => $this->que,
            'tenant_id' => $this->tenantId,
            'status' => $status,
            'application_submission_id' => $applicationSubmissionId
        ]);

        $message = [
            'tenant_id' => $this->tenantId,
            'application_submission_id' => $applicationSubmissionId,
            'status' => $status,
            'type' => 'application_new_status'
        ];

        $sqsMessage = [
            'MessageBody' => json_encode($message),
            'QueueUrl' => $this->que,
            'DelaySeconds' => 3
        ];

        $this->client->sendMessage($sqsMessage);

        return true;
    }
}
