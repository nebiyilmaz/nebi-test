<?php

declare(strict_types=1);

namespace Divido\Proxies;

use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\Exceptions\ApplicationApiException;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\RequestFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;

class ApplicationSubmissionApi
{
    use LoggerAwareTrait;

    private $baseUri;

    private $tenantId;

    private $httpClient;

    private $requestFactory;

    public function __construct(
        string $baseUri,
        string $tenantId,
        ClientInterface $httpClient = null,
        RequestFactory $requestFactory = null
    ) {
        $this->baseUri = $baseUri;
        $this->tenantId = $tenantId;
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? MessageFactoryDiscovery::find();
    }

    public function submitEvent(string $applicationId): object
    {
        $response = $this->request('post', "/event", [], ['X-Divido-Tenant-Id' => $this->tenantId], json_encode([
            'data' => [
                'event' => 'submission',
                'data' => [
                    'application_id' => $applicationId,
                ],
            ],
        ]));

        $parsed = $this->parseResponse($response);

        return $parsed->data;
    }

    private function request(string $method, string $path, array $query = [], array $headers = [], ?string $body = null): ResponseInterface
    {
        $uri = $this->baseUri . $path . '?' . http_build_query($query);

        $request = $this->requestFactory->createRequest($method, $uri, $headers, $body);
        $response = $this->httpClient->sendRequest($request);
        $statusCode = $response->getStatusCode();

        $responseBody = (string) $response->getBody();
        $response->getBody()->rewind();

        $this->logger->debug('application-submission-api-v2 request', [
            'method' => $method,
            'path' => $path,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body,
            'http_status' => $statusCode,
            'response' => $responseBody,
        ]);

        if ($statusCode >= 400 && $statusCode < 500) {
            $response = $this->parseResponse($response);

            throw new ApplicationApiException($response->message, $response->code, $response->context ?? null);
        } else if ($statusCode >= 500) {
            throw new UpstreamServiceBadResponseException('application-submission-api-v2', $response);
        }

        return $response;
    }

    private function parseResponse(ResponseInterface $response): object
    {
        $response = $response->getBody()->getContents();

        try {
            return json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Could not parse response from application-submission-api-v2. JsonException thrown', [
                'response' => $response,
            ]);

            throw new UpstreamServiceBadResponseException('application-submission-api', $response);
        }
    }
}
