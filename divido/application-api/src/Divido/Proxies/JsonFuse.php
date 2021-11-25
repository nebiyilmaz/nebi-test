<?php

namespace Divido\Proxies;

use Divido\ApiExceptions\UpstreamServiceBadResponseException;
use Divido\Exceptions\ApplicationApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class JsonFuse
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2020, Divido
 */
class JsonFuse
{
    /** @var Client $client */
    var $client;

    /** @var string $apiUrl */
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
     * @param $originalDataSet
     * @param $newDataSet
     * @return mixed
     * @throws ApplicationApiException
     * @throws GuzzleException
     * @throws UpstreamServiceBadResponseException
     */
    public function fuse($originalDataSet, $newDataSet)
    {
        $client = $this->getClient();

        $request = new Request("post", $this->apiUrl . "/fuse");

        if (!$originalDataSet) {
            $originalDataSet = (object) ['value' => []];
        }

        $payload = [
            'model' => 'applicants',
            'original_data_set' => [
                'applicants' => $originalDataSet
            ],
            'new_data_set' => [
                'applicants' => $newDataSet
            ],
        ];

        $request = $request->withBody(stream_for(json_encode($payload)));

        try {
            $response = $client->send($request);
            $json = json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            $json = @json_decode($e->getResponse()->getBody());

            if ($json->code && $json->error) {
                throw new ApplicationApiException($json->message, $json->code, $json->context ?? []);
            }
        } catch (ConnectException $e) {
            throw new UpstreamServiceBadResponseException('json-fuse', $e->getMessage());
        } catch (\Exception $e) {
            throw new UpstreamServiceBadResponseException('json-fuse', $e->getMessage());
        }

        return $json->applicants;
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
