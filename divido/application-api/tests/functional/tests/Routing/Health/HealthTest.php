<?php

namespace Divido\Test\Functional\Routing\Health;

use Divido\Test\Functional\ApiTest;

class HealthTest extends ApiTest
{
    public function test_HealthPath_Returns200_AndOKMessage()
    {
        $request = $this->createRequest('GET', '/health');
        $response = $this->getHttpClient()->send($request);

        $json = json_decode($response->getBody()->getContents(), 0);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsObject($json);
        $this->assertObjectHasAttribute('data', $json);
        $data = $json->data;
        $this->assertIsObject($data);
        $this->assertSame('ok', $data->status);
        $this->assertObjectHasAttribute('checked_at', $data);
        $this->assertSame('application-api', $data->service);
    }
}
