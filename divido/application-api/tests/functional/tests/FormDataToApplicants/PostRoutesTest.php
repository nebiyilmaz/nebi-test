<?php

namespace Divido\Test\Functional\Routing\FormDataToApplicants;

use Divido\Test\Functional\ApiTest;

class PostRoutesTest extends ApiTest
{
    /**
     * @dataProvider getApplicationsDataProvider
     */
    public function test_FormDataToApplicantsPost_Success(string $formData, string $result)
    {
        $request = $this->createRequest(
            'POST',
            '/form-data-to-applicants',
            [],
            ['X-Divido-Tenant-Id' => 'divido'],
            $formData
        );

        $response = $this->getHttpClient()->send($request);

        $this::assertSame(200, $response->getStatusCode());
        $this::assertSame(trim($result), $response->getBody()->getContents());
    }

    public function getApplicationsDataProvider(): array
    {
        return [
            [
                file_get_contents(sprintf('%s/form-data1.json', __DIR__)),
                file_get_contents(sprintf('%s/applicants1.json', __DIR__)),
            ],
        ];
    }
}
