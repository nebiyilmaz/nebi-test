<?php

namespace Divido\Proxies;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class Calculator
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2020, Divido
 */
class Calculator
{
    /**
     * @var Client $client
     */
    var $client;

    /**
     * @var string $apiUrl
     */
    var $apiUrl;

    /**
     * Calculator constructor.
     * @param string $apiUrl
     */
    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param $financeOptions
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCalculations($financeOptions)
    {
        $client = $this->getClient();

        $request = new Request("post", $this->apiUrl);

        $request = $request->withBody(stream_for(json_encode($financeOptions)));

        $response = $client->send($request);

        $json = json_decode($response->getBody()->getContents());

        return $json;
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
