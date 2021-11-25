<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Event;

use Divido\ApplicationApiSdk\Client as ApplicationApiSdk;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\Services\Application\Application;
use Divido\Services\Event\EventService;
use Divido\Services\Tenant\TenantService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var SubmissionObjectBuilder|MockObject
     */
    private $submissionObjectBuilder;

    public function setUp(): void
    {
        $this->submissionObjectBuilder = $this->createMock(SubmissionObjectBuilder::class);

        $this->eventService = new EventService(
            $this->createMock(TenantService::class),
            $this->createMock(ApplicationApiSdk::class),
            $this->submissionObjectBuilder
        );
    }

    /**
     * @param array $lenderSettings
     * @param bool $expectSupports
     *
     * @dataProvider supportsProvider
     */
    public function testSupports(array $lenderSettings, bool $expectSupports): void
    {
        $application = new Application();
        $submission = (object) [
            'lender' => (object) [
                'settings' => [
                    'generic' => (object) $lenderSettings
                ]
            ]
        ];

        $this->submissionObjectBuilder
            ->expects($this->once())
            ->method('getSubmission')
            ->with($application)
            ->willReturn($submission);

        self::assertEquals($expectSupports, $this->eventService->supports($application));
    }

    public function supportsProvider()
    {
        return [
            [[], false],
            [['supports_v2' => null], false],
            [['supports_v2' => false], false],
            [['supports_v2' => true], true],
        ];
    }
}
