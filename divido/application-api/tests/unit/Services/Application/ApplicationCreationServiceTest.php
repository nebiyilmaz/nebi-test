<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Application;

use Divido\IndexRateSdk\Client as IndexRateApiSdk;
use Divido\IndexRateSdk\IndexRate;
use Divido\LogStreamer\Logger;
use Divido\Proxies\Calculator;
use Divido\Proxies\Validation;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationCreationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationCreationServiceTest extends TestCase
{
    private const INDEX_RATE_NAME = 'foo-bar-rate';

    private const INDEX_RATE_PERCENTAGE = 0.1;

    private const INDEX_RATE_DATE = '2000-01-01';

    /**
     * @var ApplicationCreationService
     */
    private $service;

    /**
     * @var IndexRateApiSdk|MockObject
     */
    private $indexRateApiSdk;

    public function setUp(): void
    {
        $this->indexRateApiSdk = $this->createMock(IndexRateApiSdk::class);

        $this->service = new ApplicationCreationService(
            $this->createMock(\PDO::class),
            $this->createMock(Calculator::class),
            $this->createMock(Validation::class),
            $this->indexRateApiSdk
        );
        $this->service->setLogger(self::createMock(Logger::class));
    }

    /**
     * @param string|null $indexRateName
     * @param array $expected
     *
     * @dataProvider indexRateProvider
     */
    public function testGenerateFinanceSettingsIndexRate(?string $indexRateName, array $expected): void
    {
        $application = new Application();
        $application->setId('application-id');
        $application->setPurchasePrice(100000);
        $application->setDepositAmount(0);

        $financeOptionData = [
            'id' => '108341fd-e18b-4966-bccb-79f8c51cbf73',
            'app_name' => 'fooBarApp',
            'agreement_duration_months' => 12,
            'country_code' => 'GB',
            'maximum_amount' => 0,
            'minimum_amount' => 250,
            'deferral_period_months' => 0,
            'minimum_deposit_percentage' => 0,
            'maximum_deposit_percentage' => 0,
            'description' => '6 months interest free',
            'instalment_fee_amount' => 0,
            'setup_fee_amount' => 0,
            'minimum_repayment_amount' => 0,
            'minimum_repayment_percentage' => 0,
            'margin_rate_percentage' => 0,
            'interest_rate_percentage' => 0,
            'index_rate_name' => $indexRateName,
        ];

        if ($indexRateName === self::INDEX_RATE_NAME) {
            $this->indexRateApiSdk
                ->expects($this->once())
                ->method('get')
                ->with(self::INDEX_RATE_NAME)
                ->willReturn($this->getIndexRate(self::INDEX_RATE_PERCENTAGE, new \DateTime(self::INDEX_RATE_DATE)));
        }

        $financeOption = json_decode(json_encode($financeOptionData));
        $financeSettings = $this->service->generateFinanceSettings($application, $financeOption);

        $this->assertEquals($expected, $financeSettings['plan']['index_rate']);
    }

    /**
     * @return array
     */
    public function indexRateProvider(): array
    {
        return [
            ['', ['percentage' => 0, 'registered_at' => null]],
            [null, ['percentage' => 0, 'registered_at' => null]],
            [
                self::INDEX_RATE_NAME,
                ['percentage' => self::INDEX_RATE_PERCENTAGE, 'registered_at' => self::INDEX_RATE_DATE]
            ],
        ];
    }

    /**
     * @param float $percentage
     * @param \DateTime $dateTime
     * @return IndexRate
     */
    private function getIndexRate(float $percentage, \DateTime $dateTime): IndexRate
    {
        $class = new \ReflectionClass(IndexRate::class);
        $constructor = $class->getConstructor();
        $constructor->setAccessible(true);
        $object = $class->newInstanceWithoutConstructor();
        $constructor->invoke($object, $percentage, $dateTime);

        return $object;
    }
}
