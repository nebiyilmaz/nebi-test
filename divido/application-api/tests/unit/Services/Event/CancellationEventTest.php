<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Event;

use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApi;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Cancellation\Cancellation;
use Divido\Services\Cancellation\CancellationService;
use Divido\Services\Event\CancellationEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancellationEventTest extends TestCase
{
    private const APPLICATION_ID = '-application-id-';
    private const APPLICATION_CANCELLATION_ID = '-application-cancellation-id-';

    /**
     * @var ApplicationService|MockObject
     */
    private $applicationService;

    /**
     * @var CancellationService|MockObject
     */
    private $cancellationService;

    /**
     * @var LenderCommunicationApi|MockObject
     */
    private $lenderCommunicationApi;

    /**
     * @var CancellationEvent
     */
    private $service;

    public function setUp(): void
    {
        $this->applicationService = $this->createMock(ApplicationService::class);
        $this->cancellationService = $this->createMock(CancellationService::class);

        $this->lenderCommunicationApi = $this->createMock(LenderCommunicationApi::class);
        $this->service = new CancellationEvent(
            $this->applicationService,
            $this->cancellationService,
            $this->createMock(ApplicationObjectBuilder::class),
            $this->createMock(SubmissionObjectBuilder::class),
            $this->lenderCommunicationApi
        );
    }

    public function testCancellationOfApplication(): void
    {
        $application = new Application();
        $application
            ->setId(self::APPLICATION_ID)
            ->setPurchasePrice(100000)
            ->setDepositAmount(0)
            ->setActivatedAmount(0)
            ->setActivatedAmountTotal(0)
            ->setCancelledAmount(100000);
        $this->applicationService->method('getOne')->willReturn($application);

        $now = new \DateTime();
        $cancellation = new Cancellation();
        $cancellation
            ->setId(self::APPLICATION_CANCELLATION_ID)
            ->setApplicationId(self::APPLICATION_ID)
            ->setStatus('REQUESTED')
            ->setProductData([])
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
        $this->cancellationService->method('getOne')->willReturn($cancellation);
        $this->cancellationService->method('getAll')->willReturn([$cancellation]);

        $this->lenderCommunicationApi
            ->expects(self::once())
            ->method('cancellation')
            ->willReturn((object) [
                'data' => (object) [
                    'id' => self::APPLICATION_CANCELLATION_ID,
                    'status' => 'CANCELLED',
                    'amount' => $cancellation->getAmount(),
                    'reference' => $cancellation->getReference(),
                    'comment' => $cancellation->getComment(),
                ]
            ]);

        $this->applicationService
            ->expects($this->once())
            ->method('createApplicationStatusRequest')
            ->with($application, 'CANCELLED');

        $this->service->event(self::APPLICATION_CANCELLATION_ID);

        self::assertEquals('CANCELLED', $cancellation->getStatus());
    }
}
