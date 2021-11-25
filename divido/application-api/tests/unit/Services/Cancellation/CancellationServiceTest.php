<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Cancellation;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationCancellationNotPossibleException;
use Divido\Proxies\Platform;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationDatabaseInterface;
use Divido\Services\Cancellation\CancellationService;
use Divido\Services\Event\EventService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class CancellationServiceTest extends MockeryTestCase
{
    private const APPLICATION_ID = '-application-id-';

    /**
     * @var ApplicationService|MockInterface
     */
    private $applicationService;

    /**
     * @var EventService|MockInterface
     */
    private $eventService;

    /**
     * @var Platform|MockInterface
     */
    private $platformProxy;

    /**
     * @var CancellationService
     */
    private $service;

    public function setUp(): void
    {
        $this->applicationService = \Mockery::spy(ApplicationService::class);
        $this->eventService = \Mockery::mock(EventService::class);
        $this->platformProxy = \Mockery::mock(Platform::class);

        $this->service = new CancellationService(
            $this->applicationService,
            $this->eventService,
            $this->createMock(CancellationDatabaseInterface::class),
            $this->platformProxy,
        );
    }

    public function testCreateCancellation(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(true);

        $this->eventService
            ->shouldReceive('newEvent')
            ->once()
            ->with('cancellation', \Mockery::hasKey('application_cancellation_id'));

        $this->service->create($this->getCancellation());
    }

    public function testCreateCancellationViaPlatform(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->once()
            ->with(Platform::CANCEL_APPLICATION, \Mockery::hasKey('id'));

        $this->service->create($this->getCancellation());
    }

    public function testCreateCancellationViaPlatformException(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->andThrow($this->createMock(AbstractException::class));

        $this->expectException(ApplicationCancellationNotPossibleException::class);
        $this->service->create($this->getCancellation());
    }

    /**
     * @return bool[][]
     */
    public function isSupportedProvider(): array
    {
        return [[true], [false]];
    }

    private function willFetchAnApplication(): void
    {
        $application = new Application();
        $application
            ->setId(self::APPLICATION_ID)
            ->setStatus('PROPOSAL')
            ->setPurchasePrice(10000)
            ->setDepositAmount(0)
            ->setActivatedAmount(0)
            ->setCancelledAmount(0);

        $this->applicationService
            ->shouldReceive('getOne')
            ->once()
            ->andReturn($application);
    }

    private function getCancellation(): Cancellation
    {
        $cancellation = new Cancellation();
        $cancellation
            ->setApplicationId(self::APPLICATION_ID)
            ->setStatus('REQUESTED');

        return $cancellation;
    }
}
