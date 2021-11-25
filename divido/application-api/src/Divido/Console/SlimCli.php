<?php

namespace Divido\Console;

use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

/**
 * Class SlimCli
 *
 * Provides a Slim 3 environment for use in CLI.
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2017, Divido
 */
class SlimCli
{
    public $testingMethods = array('get', 'post', 'put', 'delete');

    /**
     * @var App
     */
    private $app;

    public function __construct()
    {
        $this->app = require __DIR__ . '/../../../app.php';
    }

    /**
     * Executes a slim route request using a mock environment.
     *
     * Returns the slim response.  Called internally.
     *
     * @param $method
     * @param $path
     * @param array $queryString
     * @param string $payload
     * @param array $optionalHeaders
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function request($method, $path, $queryString = [], $payload = "", $optionalHeaders = array())
    {
        $headers = new Headers();
        foreach ($optionalHeaders as $k => $v) {
            $headers->add("HTTP_" . strtoupper(preg_replace("/-/", "_", $k)), $v);
        }

        $body = new Body(fopen('php://temp', 'r+'));
        if ($payload != "") {
            $body->write($payload);
            $body->rewind();
            $headers->add("Content-Type", "application/json");
        }

        $request = new Request(
            $method,
            Uri::createFromString('http://127.0.0.1' . $path . "?" . http_build_query($queryString)),
            $headers,
            [],
            [],
            $body
        );
        $response = new Response();

        $response = $this->app->process($request, $response);

        return $response;
    }
}
