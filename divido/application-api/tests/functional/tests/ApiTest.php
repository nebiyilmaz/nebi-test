<?php

namespace Divido\Test\Functional;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 *
 * Abstract only; contains setup for truncating databases and clearing out fake server
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 */
abstract class ApiTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var FakeServer
     */
    protected $fakeServer;

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->client = new Client([
            'http_errors' => false,
        ]);

        DockerFunctionalTestsHelper::truncatePlatformDbTables();

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        DockerFunctionalTestsHelper::runDbSeed($pdo, 'add_base_data');

        $fakeServerDetails = DockerFunctionalTestsHelper::discoverFakeServerHost();
        $this->fakeServer = new FakeServer($fakeServerDetails['host'], $fakeServerDetails['port']);
        $this->fakeServer->clearExpectations();
        $this->fakeServer->clearRequests();

        parent::setUp();
    }

    /**
     * @return Client
     */
    protected function getHttpClient()
    {
        return $this->client;
    }

    /**
     * Make request to our functional tests server
     *
     * @param $method
     * @param string $path
     * @param array $query
     * @param array $headers
     * @param null $payload
     * @return Request
     * @throws \Exception
     */
    protected function createRequest($method, $path = "/", $query = [], $headers = [], $payload = null)
    {
        $service = DockerFunctionalTestsHelper::discoverAppHost();

        $url = new Uri();
        $url = $url->withScheme('http')
            ->withHost($service['host'])
            ->withPort($service['port'])
            ->withPath($path)
            ->withQuery(http_build_query($query, null, '&'));

        return new Request($method, $url->__toString(), $headers, $payload);
    }
}
