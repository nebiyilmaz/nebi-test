<?php

namespace Divido\Proxies;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Validation
{
    var $apiUrl;

    var $apiKey;

    var $client;

    /**
     * Calculator constructor.
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $firstName
     * @param string $countryCode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function suggestGender(string $firstName, string $countryCode)
    {
        $client = $this->getClient();

        $url = $this->apiUrl . "/suggest/gender?apikey=" . $this->apiKey . "&first_name=" . urlencode(trim($firstName)) . "&country_code=" . $countryCode;

        $request = new Request("get", $url);
        $response = $client->send($request);

        $json = json_decode($response->getBody()->getContents());

        if ($json->result->gender != "unknown") {
            return $json->result->gender;
        }

        return null;
    }

    /**
     * @param string $text
     * @param string $sanityValue
     * @param string $countryCode
     * @return |null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function suggestAddress(string $text, $sanityValue = null, string $countryCode)
    {
        $client = $this->getClient();

        $url = $this->apiUrl . "/suggest/address?apikey=" . $this->apiKey . "&address=" . urlencode(trim($text)) . "&sanity_value=" . urlencode(trim($sanityValue)) . "&country_code=" . $countryCode;

        $request = new Request("get", $url);

        try {
            $response = $client->send($request);
        } catch(\Exception $e) {
            return null;
        }

        $json = json_decode($response->getBody()->getContents());

        if (!empty($json->result->postcode)) {
            return $json->result;
        }

        return null;
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
