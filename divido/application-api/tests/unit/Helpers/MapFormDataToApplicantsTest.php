<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Helpers;

use Divido\Helpers\MapFormDataToApplicants;
use PHPUnit\Framework\TestCase;

class MapFormDataToApplicantsTest extends TestCase
{
    /**
     * @dataProvider getApplicationsDataProvider
     */
    public function testGetApplicants(string $applicationJson, string $applicantsJson, string $resultJson)
    {
        $applicationData = json_decode($applicationJson);
        $applicantsData = json_decode($applicantsJson);

        $mapFormDataToApplicants = new MapFormDataToApplicants();

        $applicants = $mapFormDataToApplicants->getApplicants($applicationData->data->form_data, $applicantsData);

        // This assertion is not entirely useful as returned value may not be mapped correctly,
        // `getApplicants` method does not map all the fields e.g. postcode is not updated.
        // @TODO: This has to be investigated further.
        $this::assertSame(trim($resultJson), json_encode($applicants));
    }

    public function getApplicationsDataProvider(): array
    {
        return [
            [
                file_get_contents(sprintf('%s/application1.json', __DIR__)),
                file_get_contents(sprintf('%s/applicants1.json', __DIR__)),
                file_get_contents(sprintf('%s/result1.json', __DIR__)),
            ],
            [
                file_get_contents(sprintf('%s/application2.json', __DIR__)),
                file_get_contents(sprintf('%s/applicants2.json', __DIR__)),
                file_get_contents(sprintf('%s/result2.json', __DIR__)),
            ],
            [
                file_get_contents(sprintf('%s/application3.json', __DIR__)),
                file_get_contents(sprintf('%s/applicants3.json', __DIR__)),
                file_get_contents(sprintf('%s/result3.json', __DIR__)),
            ],
        ];
    }
}
