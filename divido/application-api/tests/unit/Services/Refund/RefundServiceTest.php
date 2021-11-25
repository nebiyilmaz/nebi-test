<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Refund;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationRefundNotPossibleException;
use Divido\Proxies\Platform;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Divido\Services\Refund\Refund;
use Divido\Services\Refund\RefundDatabaseInterface;
use Divido\Services\Refund\RefundService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class RefundServiceTest extends MockeryTestCase
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
     * @var RefundService
     */
    private $service;

    public function setUp(): void
    {
        $this->applicationService = \Mockery::spy(ApplicationService::class);
        $this->eventService = \Mockery::mock(EventService::class);
        $this->platformProxy = \Mockery::mock(Platform::class);

        $this->service = new RefundService(
            $this->applicationService,
            $this->eventService,
            $this->createMock(RefundDatabaseInterface::class),
            $this->platformProxy,
        );
    }

    public function testCreateRefund(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(true);

        $this->eventService
            ->shouldReceive('newEvent')
            ->once()
            ->with('refund', \Mockery::hasKey('application_refund_id'));

        $this->service->create($this->getRefund());
    }

    public function testCreateRefundViaPlatform(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->once()
            ->with(Platform::REFUND_APPLICATION, \Mockery::hasKey('id'));

        $this->service->create($this->getRefund());
    }

    public function testCreateRefundViaPlatformException(): void
    {
        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->andThrow($this->createMock(AbstractException::class));

        $this->expectException(ApplicationRefundNotPossibleException::class);
        $this->service->create($this->getRefund());
    }

    private function willFetchAnApplication(): void
    {
        $application = new Application();
        $application
            ->setId(self::APPLICATION_ID)
            ->setStatus('ACTIVATED')
            ->setActivatedAmount(10000)
            ->setRefundedAmount(0);

        $this->applicationService
            ->shouldReceive('getOne')
            ->once()
            ->andReturn($application);
    }

    private function getRefund(): Refund
    {
        $refund = new Refund();
        $refund
            ->setApplicationId(self::APPLICATION_ID)
            ->setStatus('REQUESTED');

        return $refund;
    }
}
