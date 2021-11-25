<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Proxies;

use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\LogStreamer\Logger;
use Divido\Proxies\ApplicationSubmissionApi as ApplicationSubmissionApiProxy;
use Http\Message\RequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ApplicationSubmissionApiTest extends TestCase
{
    public function test_CallingApplicationSubmissionApi_WithValidPayload_HandlesSuccessfulResponse()
    {
        $stream = self::createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode([
            'data' => [
                'executed_at' => [
                    'date' => '2006-01-02T15:04:05-07:00',
                    'timezone' => 'UTC',
                ],
            ],
        ]));

        $response = self::createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        $responseFactory = self::createMock(ResponseFactoryInterface::class);
        $responseFactory->method('createResponse')->willReturn($response);

        $httpClient = new \Http\Mock\Client($responseFactory);

        $requestFactory = self::createMock(RequestFactory::class);
        $requestFactory->method('createRequest')->willReturn($this->createMock(RequestInterface::class));

        $applicationSubmissionApiProxy = new ApplicationSubmissionApiProxy(
            '-application-submission-api-host-',
            'divido',
            $httpClient,
            $requestFactory
        );

        $logger = self::createMock(Logger::class);
        $applicationSubmissionApiProxy->setLogger($logger);

        $response = $applicationSubmissionApiProxy->submitEvent('-application-id-');

        self::assertEquals("2006-01-02T15:04:05-07:00", $response->executed_at->date);
        self::assertEquals("UTC", $response->executed_at->timezone);
    }

    public function test_CallingApplicationSubmissionApi_WithErrorResponse_HandlesAsError()
    {
        $stream = self::createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode([
            'error' => true,
            'code' => 500001,
            'message' => 'internal server error',
        ]));

        $response = self::createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getBody')->willReturn($stream);

        $responseFactory = self::createMock(ResponseFactoryInterface::class);
        $responseFactory->method('createResponse')->willReturn($response);

        $httpClient = new \Http\Mock\Client($responseFactory);

        $requestFactory = self::createMock(RequestFactory::class);
        $requestFactory->method('createRequest')->willReturn($this->createMock(RequestInterface::class));

        $applicationSubmissionApiProxy = new ApplicationSubmissionApiProxy(
            '-application-submission-api-host-',
            'divido',
            $httpClient,
            $requestFactory
        );

        $logger = self::createMock(Logger::class);
        $applicationSubmissionApiProxy->setLogger($logger);

        $this->expectException(UpstreamServiceBadResponseException::class);

        $applicationSubmissionApiProxy->submitEvent('-application-id-');
    }
}
