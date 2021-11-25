<?php

namespace Divido\Test\Unit\Routing;

use Divido\LogStreamer\Logger;
use function GuzzleHttp\Psr7\stream_for;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Uri;

/**
 * Class RouteTestCase
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2017, Divido
 */
class RouteTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();

        $this->container['Logger'] = self::createMock(Logger::class);
    }

    /**
     * @param $method
     * @param array $attributes
     * @param array $query
     * @param array $headers
     * @param null $body
     * @return Request
     * @internal param string $path
     */
    protected function createRequest($method, $attributes = [], $query = [], $headers = [], $body = null)
    {
        $requestMethod = strtoupper($method);
        $query = http_build_query($query, null, '&');

        $requestUrl = new Uri('http', 'test', 1, '/', $query);

        $requestHeaders = new Headers();
        foreach ($headers as $k=>$v) {
            $requestHeaders->add($k, $v);
        }

        $requestBody = stream_for($body ?? '{}');

        $request = new Request($requestMethod, $requestUrl, $requestHeaders, [], [], $requestBody);
        $request = $request->withAttributes($attributes);

        return $request;
    }
}
