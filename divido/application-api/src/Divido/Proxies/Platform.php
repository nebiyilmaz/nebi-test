<?php

namespace Divido\Proxies;

use Divido\ApiExceptions\InternalServerErrorException;
use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Platform
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class Platform
{
    use LoggerAwareTrait;

    public const ACTIVATE_APPLICATION = 'activate_application';

    public const CANCEL_APPLICATION = 'cancel_application';

    public const REFUND_APPLICATION = 'refund_application';

    private const TRIGGERS = [
        self::ACTIVATE_APPLICATION => '/v1/admin/trigger-activation',
        self::CANCEL_APPLICATION => '/v1/admin/trigger-cancellation',
        self::REFUND_APPLICATION => '/v1/admin/trigger-refund',
    ];

    /**
     * @var array
     */
    private $host;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $host
     * @param string $apiKey
     * @param Client $client
     */
    public function __construct(string $host, string $apiKey, $client = null)
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    /**
     * @param string $trigger
     * @param array $data
     *
     * @return mixed
     *
     * @throws InternalServerErrorException
     * @throws UpstreamServiceBadResponseException
     * @throws \Exception
     */
    public function trigger(string $trigger, array $data = [])
    {
        $triggerPath = self::TRIGGERS[$trigger];

        if ($triggerPath === null) {
            throw new InternalServerErrorException("trigger {$trigger} not recognised");
        }

        $data = array_merge($data, ['api_key' => $this->apiKey]);

        try {
            $result = $this->getClient()->request('post', $this->host . $triggerPath, [
                'json' => $data,
            ]);
            $result = json_decode($result->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new UpstreamServiceBadResponseException('platform', $e->getMessage());
        }

        if ($result['status'] === 'error') {

            $error = $result['error'] ?? 'unknown reason';

            // ignore not-implemented errors
            if ($error === 'not_implemented') {
                return;
            }

            $this->logger->error("trigger failed: {$trigger}", [
                'error' => $error,
                'note' => 'error has come from platform',
            ]);

            throw new UpstreamServiceBadResponseException('platform', $error);
        }

        return $result['result'];
    }

    /**
     * Get the Guzzle client for this instance
     *
     * If the Guzzle client has not yet been created, then a new one will be
     * create before returning
     *
     * @return Client
     */
    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }
}
