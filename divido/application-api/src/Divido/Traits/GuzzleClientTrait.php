<?php

namespace Divido\Traits;

use GuzzleHttp\Client;

/**
 * Trait GuzzleClientTrait
 *
 */
trait GuzzleClientTrait
{
    private $guzzleClient;

    /**
     * Set the Guzzle client on this object instance
     *
     * @param $guzzleClient
     */
    public function setGuzzleClient($guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Get the Guzzle client for this instance
     *
     * If the Guzzle client has not yet been created, then a new one will be
     * create before returning
     *
     * @return Client
     */
    private function getGuzzleClient()
    {
        if (!$this->guzzleClient) {
            $this->guzzleClient = new Client();
        }

        return $this->guzzleClient;
    }
}
