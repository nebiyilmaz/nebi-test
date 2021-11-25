<?php

namespace Divido\Proxies;

use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\Services\Application\Application;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PDO;
use Psr\Log\LoggerAwareTrait;

/**
 * @property  client
 */
class Webhook
{
    use LoggerAwareTrait;

    /** @var Client $client */
    var $client;

    /** @var string $apiUrl */
    var $baseUri;

    /** @var PDO $db */
    var $db;

    /**
     * Calculator constructor.
     * @param string $baseUri
     * @param PDO $db
     */
    public function __construct(string $baseUri, PDO $db)
    {
        $this->baseUri = $baseUri;
        $this->db = $db;
    }

    /**
     * @param $metadata
     * @return object
     */
    public function getPublicMetadata($metadata)
    {
        $publicMetadata = [];

        foreach ($metadata as $key => $value) {
            if (substr($key, 0, 1) != '.') {
                $publicMetadata[$key] = $value;
            }
        }

        return (object) $publicMetadata;
    }

    /**
     * @param $event
     * @param Application $application
     * @return mixed
     * @throws ResourceNotFoundException
     */
    public function send($event, Application $application)
    {
        $settings = $this->getMerchantSettings($application);

        if (count($settings['urls']) == 0 && !$application->getMerchantResponseUrl()) {
            return false;
        }

        $applicants = $application->getApplicants();

        $firstName = "";
        $lastName = "";
        $emailAddress = "";
        $phoneNumber = "";

        $personalDetails = (!empty($applicants->value[0]->value->personal_details->value)) ? $applicants->value[0]->value->personal_details->value : null;

        if (!empty($personalDetails->first_name->value)) {
            $firstName = $personalDetails->first_name->value;
        }
        if (!empty($personalDetails->last_name->value)) {
            $lastName = $personalDetails->last_name->value;
        }

        $contactDetails = (!empty($applicants->value[0]->value->contact_details->value)) ? $applicants->value[0]->value->contact_details->value : null;

        if (!empty($contactDetails->phone_numbers->value[0]->value)) {
            $phoneNumber = $contactDetails->phone_numbers->value[0]->value;
        }

        if (!empty($contactDetails->email_addresses->value[0]->value)) {
            $emailAddress = $contactDetails->email_addresses->value[0]->value;
        }

        $headers = [
            'Content-Type' => 'application/json',
            "X-DIVIDO-PRODUCTION" => (DIVIDO_APPLICATION_ENVIRONMENT == 'production') ? "true" : "false",
            "X-DIVIDO-MERCHANT" => $application->getMerchantId(),
            'X-DIVIDO-TENANT-ID' => $application->getTenantId()
        ];

        $webhook = json_encode([
            'event' => $event,
            'status' => $application->getStatus(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumber' => $phoneNumber,
            'emailAddress' => $emailAddress,
            'application' => $application->getId(),
            'reference' => $application->getMerchantReference(),
            'metadata' => $this->getPublicMetadata($application->getMetadata())
        ]);

        if ($settings['shared_secret']) {
            $hmac = base64_encode(hash_hmac('sha256', $webhook, $settings['shared_secret'], true));
            $headers["X-DIVIDO-HMAC-SHA256"] = $hmac;
        }

        $payload = [
            'request' => [
                'payload' => $webhook,
                'headers' => $headers,
                'url' => null
            ],
            'fallback' => [
                'email_address' => 'anders.hallsten@divido.com'
            ],
            'tenant' => $application->getTenantId(),
            'internal' => false,
            'meta_keys' => (object) [],
            'delivery_configuration' => [
                'deliver_at' => null
            ]
        ];

        if ($application->getMerchantResponseUrl()) {
            $this->post($payload, $application->getMerchantResponseUrl());
        }
        if (count($settings['urls'])) {
            foreach ($settings['urls'] as $url) {
                $this->post($payload, $url);
            }
        }

        return true;
    }

    public function post($payload, $url)
    {
        if (empty($url)) {
            return false;
        }

        $client = $this->getClient();

        $payload['request']['url'] = $url;

        try {
            $this->logger->debug('send webhook', ['payload' => $payload]);
            $client->post('/webhook', [
                'json' => $payload
            ]);
        } catch (ClientException $e) {
            $this->logger->error('error sending webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        } catch (Exception $e) {
            $this->logger->error('error sending webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * @param Application $application
     * @return array
     * @throws ResourceNotFoundException
     */
    public function getMerchantSettings(Application $application)
    {
        /**
         * Todo:
         * This is completely of of scope for this service, but webhooks will soon
         * move into it's own service, so for now this is ok even if it's ugly...
         * /Anders 2019-09-19
         */

        $statement = $this->db->prepare('SELECT 
            m.`settings`, m.`shared_secret`
          FROM `merchant` AS `m`
          WHERE `m`.`deleted_at` IS NULL 
            AND `m`.`id` = :id');

        $statement->execute([
            ':id' => $application->getMerchantId()
        ]);

        if (!$statement->rowCount()) {
            throw new ResourceNotFoundException('merchant', 'id', $application->getMerchantId());
        }

        $data = $statement->fetch();

        $settings = json_decode($data->settings);

        $urls = [];

        /**
         * Todo:
         * this is really shit, the merchant settings object should change and this should move
         * to a separate service.
         */
        if (!empty($settings->notifications->webhooks->active) && $settings->notifications->webhooks->active && !empty($settings->notifications->webhooks->urls)) {
            foreach ($settings->notifications->webhooks->urls as $settingsUrls) {
                $_urls = explode(",", $settingsUrls);
                foreach ($_urls as $url) {
                    $urls[] = trim($url);
                }
            }
        }

        return [
            'shared_secret' => $data->shared_secret,
            'urls' => $urls
        ];
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => $this->baseUri
            ]);
        }

        return $this->client;
    }
}
