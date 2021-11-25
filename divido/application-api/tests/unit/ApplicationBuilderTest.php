<?php

namespace Divido\Test\Unit;

use Divido\ApplicationApiSdk\Client as ApplicationApiSdk;
use Divido\ApplicationBuilder;
use Divido\Handlers\ErrorHandler;
use Divido\Handlers\NotAllowedHandler;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\IndexRateSdk\Client as IndexRateApiSdk;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSDK;
use Divido\LogStreamer\Logger;
use Divido\MerchantApiSdk\Client as MerchantApiSDK;
use Divido\Proxies\ApplicationSubmissionApi as ApplicationSubmissionApiProxy;
use Divido\Proxies\Calculator;
use Divido\Proxies\JsonFuse;
use Divido\Proxies\Platform;
use Divido\Proxies\Validation;
use Divido\Proxies\Webhook;
use Divido\Services\Activation\ActivationDatabaseInterface;
use Divido\Services\Activation\ActivationService;
use Divido\Services\AlternativeOffer\AlternativeOfferDatabaseInterface;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Services\Application\ApplicationCreationService;
use Divido\Services\Application\ApplicationDatabaseInterface;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Application\ApplicationSubmissionService;
use Divido\Services\Cancellation\CancellationDatabaseInterface;
use Divido\Services\Cancellation\CancellationService;
use Divido\Services\Event\ActivationEvent;
use Divido\Services\Event\CancellationEvent;
use Divido\Services\Event\EventDispatcherService;
use Divido\Services\Event\EventService;
use Divido\Services\Event\RefundEvent;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\Health\HealthDatabaseInterface;
use Divido\Services\Health\HealthService;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Refund\RefundDatabaseInterface;
use Divido\Services\Refund\RefundService;
use Divido\Services\Signatory\SignatoryDatabaseInterface;
use Divido\Services\Signatory\SignatoryService;
use Divido\Services\Submission\SubmissionDatabaseInterface;
use Divido\Services\Submission\SubmissionService;
use Divido\Services\Tenant\Tenant;
use Divido\Services\Tenant\TenantDatabaseInterface;
use Divido\Services\Tenant\TenantService;
use Divido\WaterfallApiSdk\Client as WaterfallApiSDK;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Noodlehaus\Config;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slim\Container;
use Slim\Http\Request;

class ApplicationBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function setMockConfig()
    {
        $config = [
            "redis.main.host" => "0.0.0.1",
            "redis.main.port" => 6379,
            "kong.admin.protocol" => "http",
            "kong.admin.host" => "0.0.0.1",
            "kong.admin.port" => 1,

        ];

        foreach ($config as $k => $v) {
            putenv('DIVIDO_' . strtoupper(str_replace(".", "_", $k)) . '=' . $v);
        }
    }

    public function test_ApplicationBuilder_NoContainer()
    {
        $applicationBuilder = new ApplicationBuilder();
        $app = $applicationBuilder->build();

        $container = $app->getContainer();

        self::assertInstanceOf(Container::class, $container);
    }

    public function test_ApplicationBuilder_FillsContainer_WithDependencies()
    {
        $this->setMockConfig();

        $applicationBuilder = new ApplicationBuilder();

        $app = $applicationBuilder::make();

        $container = $app->getContainer();

        $container['Database.Platform.Master'] = \Mockery::spy(\PDO::class);
        $container['Database.Platform.ReadReplica'] = \Mockery::spy(\PDO::class);
        $container['Redis'] = \Mockery::spy(\Redis::class);
        $container['environment'] = \Mockery::spy(Request::class);
        $container['environment']->shouldReceive('get')
            ->with('PARSED_TENANT_ID')
            ->andReturn('-tenant-id-');

        $container['Service.Tenant'] = \Mockery::spy(TenantService::class);
        $container['Service.Tenant']->shouldReceive('getOne')
            ->with()
            ->andReturn((new Tenant())->setId('-tenant-'));

        // Logger assertions
        self::assertArrayHasKey('Logger', $container);
        self::assertInstanceOf(LoggerInterface::class, $container->get('Logger'));
        self::assertInstanceOf(Logger::class, $container->get('Logger'));

        // Error Handler assertions
        self::assertArrayHasKey('phpErrorHandler', $container);
        self::assertInstanceOf(ErrorHandler::class, $container->get('phpErrorHandler'));
        self::assertArrayHasKey('errorHandler', $container);
        self::assertInstanceOf(ErrorHandler::class, $container->get('errorHandler'));
        self::assertArrayHasKey('notAllowedHandler', $container);
        self::assertInstanceOf(NotAllowedHandler::class, $container->get('notAllowedHandler'));

        // Config assertions
        self::assertArrayHasKey('Config', $container);
        self::assertInstanceOf(Config::class, $container->get('Config'));

        // Client assertions
        self::assertArrayHasKey('Redis', $container);
        self::assertInstanceOf(\Redis::class, $container->get('Redis'));

        // Client assertions
        self::assertArrayHasKey('Database.Platform.ReadReplica', $container);
        self::assertInstanceOf(\PDO::class, $container->get('Database.Platform.Master'));

        self::assertArrayHasKey('Database.Platform.ReadReplica', $container);
        self::assertInstanceOf(\PDO::class, $container->get('Database.Platform.ReadReplica'));

        // Service assertions
        self::assertArrayHasKey('Service.Health', $container);
        self::assertInstanceOf(HealthService::class, $container->get('Service.Health'));
        self::assertArrayHasKey('Service.HealthDatabaseInterface', $container);
        self::assertInstanceOf(HealthDatabaseInterface::class, $container->get('Service.HealthDatabaseInterface'));
        self::assertArrayHasKey('Service.Tenant', $container);
        self::assertInstanceOf(TenantService::class, $container->get('Service.Tenant'));
        self::assertArrayHasKey('Service.TenantDatabaseInterface', $container);
        self::assertInstanceOf(TenantDatabaseInterface::class, $container->get('Service.TenantDatabaseInterface'));
        self::assertArrayHasKey('Service.Application', $container);
        self::assertInstanceOf(ApplicationService::class, $container->get('Service.Application'));
        self::assertArrayHasKey('Service.ApplicationDatabaseInterface', $container);
        self::assertInstanceOf(ApplicationDatabaseInterface::class, $container->get('Service.ApplicationDatabaseInterface'));
        self::assertArrayHasKey('Service.ApplicationCreation', $container);
        self::assertInstanceOf(ApplicationCreationService::class, $container->get('Service.ApplicationCreation'));
        self::assertArrayHasKey('Service.ApplicationSubmission', $container);
        self::assertInstanceOf(ApplicationSubmissionService::class, $container->get('Service.ApplicationSubmission'));
        self::assertArrayHasKey('Service.Signatory', $container);
        self::assertInstanceOf(SignatoryService::class, $container->get('Service.Signatory'));
        self::assertArrayHasKey('Service.SignatoryDatabaseInterface', $container);
        self::assertInstanceOf(SignatoryDatabaseInterface::class, $container->get('Service.SignatoryDatabaseInterface'));
        self::assertArrayHasKey('Service.Activation', $container);
        self::assertInstanceOf(ActivationService::class, $container->get('Service.Activation'));
        self::assertArrayHasKey('Service.ActivationDatabaseInterface', $container);
        self::assertInstanceOf(ActivationDatabaseInterface::class, $container->get('Service.ActivationDatabaseInterface'));
        self::assertArrayHasKey('Service.Cancellation', $container);
        self::assertInstanceOf(CancellationService::class, $container->get('Service.Cancellation'));
        self::assertArrayHasKey('Service.CancellationDatabaseInterface', $container);
        self::assertInstanceOf(CancellationDatabaseInterface::class, $container->get('Service.CancellationDatabaseInterface'));
        self::assertArrayHasKey('Service.Refund', $container);
        self::assertInstanceOf(RefundService::class, $container->get('Service.Refund'));
        self::assertArrayHasKey('Service.Submission', $container);
        self::assertInstanceOf(SubmissionService::class, $container->get('Service.Submission'));
        self::assertArrayHasKey('Service.SubmissionDatabaseInterface', $container);
        self::assertInstanceOf(SubmissionDatabaseInterface::class, $container->get('Service.SubmissionDatabaseInterface'));
        self::assertArrayHasKey('Service.RefundDatabaseInterface', $container);
        self::assertInstanceOf(RefundDatabaseInterface::class, $container->get('Service.RefundDatabaseInterface'));
        self::assertArrayHasKey('Service.AlternativeOffer', $container);
        self::assertInstanceOf(AlternativeOfferService::class, $container->get('Service.AlternativeOffer'));
        self::assertArrayHasKey('Service.AlternativeOfferDatabaseInterface', $container);
        self::assertInstanceOf(AlternativeOfferDatabaseInterface::class, $container->get('Service.AlternativeOfferDatabaseInterface'));
        self::assertArrayHasKey('Service.Event', $container);
        self::assertInstanceOf(EventService::class, $container->get('Service.Event'));
        self::assertArrayHasKey('Service.EventDispatcher', $container);
        self::assertInstanceOf(EventDispatcherService::class, $container->get('Service.EventDispatcher'));
        self::assertArrayHasKey('Service.FormConfiguration', $container);
        self::assertInstanceOf(FormConfigurationService::class, $container->get('Service.FormConfiguration'));
        self::assertArrayHasKey('Service.LenderCall', $container);
        self::assertInstanceOf(LenderCallService::class, $container->get('Service.LenderCall'));
        self::assertArrayHasKey('Event.Cancellation', $container);
        self::assertInstanceOf(CancellationEvent::class, $container->get('Event.Cancellation'));
        self::assertArrayHasKey('Event.Activation', $container);
        self::assertInstanceOf(ActivationEvent::class, $container->get('Event.Activation'));
        self::assertArrayHasKey('Event.Refund', $container);
        self::assertInstanceOf(RefundEvent::class, $container->get('Event.Refund'));

        self::assertArrayHasKey('Proxy.Calculator', $container);
        self::assertInstanceOf(Calculator::class, $container->get('Proxy.Calculator'));
        self::assertArrayHasKey('Proxy.Webhook', $container);
        self::assertInstanceOf(Webhook::class, $container->get('Proxy.Webhook'));
        self::assertArrayHasKey('Proxy.JsonFuse', $container);
        self::assertInstanceOf(JsonFuse::class, $container->get('Proxy.JsonFuse'));
        self::assertArrayHasKey('Proxy.Validation', $container);
        self::assertInstanceOf(Validation::class, $container->get('Proxy.Validation'));
        self::assertArrayHasKey('Proxy.Platform', $container);
        self::assertInstanceOf(Platform::class, $container->get('Proxy.Platform'));
        self::assertArrayHasKey('Sdk.MerchantApi', $container);
        self::assertInstanceOf(MerchantApiSDK::class, $container->get('Sdk.MerchantApi'));
        self::assertArrayHasKey('Sdk.WaterfallApi', $container);
        self::assertInstanceOf(WaterfallApiSDK::class, $container->get('Sdk.WaterfallApi'));
        self::assertArrayHasKey('Sdk.ApplicationApi', $container);
        self::assertInstanceOf(ApplicationApiSdk::class, $container->get('Sdk.ApplicationApi'));
        self::assertArrayHasKey('Proxy.ApplicationSubmissionApi', $container);
        self::assertInstanceOf(ApplicationSubmissionApiProxy::class, $container->get('Proxy.ApplicationSubmissionApi'));
        self::assertArrayHasKey('Sdk.LenderCommunicationApi', $container);
        self::assertInstanceOf(LenderCommunicationApiSDK::class, $container->get('Sdk.LenderCommunicationApi'));
        self::assertArrayHasKey('Sdk.IndexRateApi', $container);
        self::assertInstanceOf(IndexRateApiSdk::class, $container->get('Sdk.IndexRateApi'));
        self::assertArrayHasKey('Helper.ApplicationObjectBuilder', $container);
        self::assertInstanceOf(ApplicationObjectBuilder::class, $container->get('Helper.ApplicationObjectBuilder'));
        self::assertArrayHasKey('Helper.SubmissionObjectBuilder', $container);
        self::assertInstanceOf(SubmissionObjectBuilder::class, $container->get('Helper.SubmissionObjectBuilder'));

    }
}
