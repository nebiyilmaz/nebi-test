<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Application;

use Divido\Services\Application\Application;
use Divido\Services\Tenant\Tenant;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    private const DEFAULT_URL = 'https://application-api.api.dev.divido.net/#/';

    private const V1_BASE_URL = 'https://apply.divido.com/#/';

    private const V2_BASE_URL = 'https://apply-v2.divido.com/#/';

    private const TOKEN = '-token-';

    /**
     * @dataProvider lenderProvider
     *
     * @param array $tenantSettings
     * @param array $lenderSettings
     * @param string $expectedBaseUrl
     */
    public function test_SetApplicationFormUrl(
        array $tenantSettings,
        array $lenderSettings,
        string $expectedBaseUrl
    ): void {
        $tenant = new Tenant();
        $tenant->setSettings($tenantSettings);

        $application = new Application();
        $application
            ->setToken(self::TOKEN)
            ->setApplicationFormUrl($tenant, $lenderSettings);

        self::assertSame($expectedBaseUrl . self::TOKEN, $application->getApplicationFormUrl());
    }

    public function lenderProvider(): array
    {
        $tenantSettings = [
            'urls' => [
                'application_form' => self::V1_BASE_URL,
                'application_form_v2' => self::V2_BASE_URL,
            ]
        ];

        return [
            [[], [], self::DEFAULT_URL],
            [$tenantSettings, [], self::V1_BASE_URL],
            [$tenantSettings, ['supports_v2' => false], self::V1_BASE_URL],
            [$tenantSettings, ['supports_v2' => null], self::V1_BASE_URL],
            [$tenantSettings, ['supports_v2' => true], self::V2_BASE_URL],
        ];
    }
}
