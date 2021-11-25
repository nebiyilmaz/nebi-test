<?php

namespace Divido\Test\Functional;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class FakeServer
 *
 * Super simple SDK for go-fake-http-server
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 */
class FakeServer
{
    /**
     * Host for the fake server
     *
     * @var string
     */
    private $host;

    /**
     * Port for the fake server
     *
     * @var int
     */
    private $port;

    /**
     * @var Base URL for fake server
     */
    private $url;

    /**
     * FakeServer constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->url = vsprintf('http://%s:%s', [
            $this->host,
            $this->port,
        ]);
    }

    /**
     * Sends a request to the fake http server
     *
     * @param Request $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest(Request $request)
    {
        $client = new Client();
        $response = $client->send($request);

        return $response;
    }

    /**
     * Returns the last request made to the fake http server
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLastRequest()
    {
        $request = new Request('GET', $this->url . '/requests/last');
        $response = $this->sendRequest($request);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Returns all requests made to the fake http server
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequests()
    {
        $request = new Request('GET', $this->url . '/requests');
        $response = $this->sendRequest($request);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Removes request history on the fake http server
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clearRequests()
    {
        $request = new Request('DELETE', $this->url . '/requests');
        $this->sendRequest($request);
    }

    /**
     * Returns all configured expectations on the fake http server
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExpectations()
    {
        $request = new Request('GET', $this->url . '/expectations');
        $response = $this->sendRequest($request);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Removes all configured expectations from the fake http server
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function clearExpectations()
    {
        $request = new Request('DELETE', $this->url . '/expectations');
        $this->sendRequest($request);
    }

    /**
     * Returns a specific configured exception, as identified by its ID
     *
     * @param int $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExpectationById(int $id)
    {
        $request = new Request('GET', $this->url . '/expectations/' . $id);
        $response = $this->sendRequest($request);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Adds a new configured expectation to the fake http server
     *
     * @param $method
     * @param $path
     * @param array $query
     * @param array $headers
     * @param null $body
     * @param array $responses
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addExpectation($method, $path, $query = [], $headers = [], $body = null, $responses = [])
    {
        $data = [
            'method' => $method,
            'path' => $path,
            'query' => $query,
            'headers' => $headers,
            'body' => $body,
            'responses' => $responses,

        ];

        $request = new Request('POST', $this->url . '/expectations', [], json_encode($data));
        $response = $this->sendRequest($request);

        return json_decode($response->getBody()->getContents())->id;
    }
}
