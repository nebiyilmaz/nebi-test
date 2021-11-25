<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Helpers;

use Divido\Helpers\MapApplicantsToFormData;
use PHPUnit\Framework\TestCase;

class MapApplicantsToFormDataTest extends TestCase
{
    public function testGetFormData_Addresses(): void
    {
        $applicantsData = $this->loadFromFile('address_applicants.json');
        $formData = MapApplicantsToFormData::getFormData($applicantsData);

        self::assertTrue(isset($formData->addresses));

        $expectedFormData = $this->loadFromFile('address_formdata.json');
        self::assertEquals($expectedFormData->addresses, $formData->addresses);
    }

    public function testGetFormData_EmptyAddress(): void
    {
        $applicantsData = new \stdClass();
        $formData = MapApplicantsToFormData::getFormData($applicantsData);

        self::assertTrue(isset($formData->addresses));
        self::assertEmpty($formData->addresses);
    }

    private function loadFromFile(string $filename): object
    {
        $json = file_get_contents(sprintf('%s/%s', __DIR__, $filename));

        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}
