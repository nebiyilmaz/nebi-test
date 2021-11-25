<?php

namespace Divido\Bootstrap;

use Aws\Sqs\SqsClient;
use Divido\ApiExceptions\ConfigPropertyNotFoundException;
use Divido\ApplicationApiSdk\Client as ApplicationApiSdk;
use Divido\Proxies\ApplicationSubmissionApi as ApplicationSubmissionApiProxy;
use Divido\Helpers\ApplicationObjectBuilder;
use Divido\Helpers\SubmissionObjectBuilder;
use Divido\IndexRateSdk\Client as IndexRateApiSdk;
use Divido\LenderCommunicationApiSdk\Client as LenderCommunicationApiSdk;
use Divido\MerchantApiSdk\Client as MerchantApiSdk;
use Divido\Middleware\TenantMiddleware;
use Divido\Proxies\Calculator;
use Divido\Proxies\JsonFuse;
use Divido\Proxies\LenderApplicationStatusWkrProxy;
use Divido\Proxies\Platform;
use Divido\Proxies\Validation;
use Divido\Proxies\Webhook;
use Divido\Services\Activation\ActivationDatabaseInterface;
use Divido\Services\Activation\ActivationService;
use Divido\Services\AlternativeOffer\AlternativeOfferDatabaseInterface;
use Divido\Services\AlternativeOffer\AlternativeOfferService;
use Divido\Services\Application\ApplicationCreationService;
use Divido\Services\Application\ApplicationDatabaseInterface;
use Divido\Services\LenderFee\LenderFeeService;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Application\ApplicationSubmissionService;
use Divido\Services\Cancellation\CancellationDatabaseInterface;
use Divido\Services\Cancellation\CancellationService;
use Divido\Services\Deposit\DepositDatabaseInterface;
use Divido\Services\Deposit\DepositService;
use Divido\Services\Event\ActivationEvent;
use Divido\Services\Event\CancellationEvent;
use Divido\Services\Event\DepositEvent;
use Divido\Services\Event\EventDispatcherService;
use Divido\Services\Event\EventService;
use Divido\Services\Event\RefundEvent;
use Divido\Services\FormConfiguration\FormConfigurationService;
use Divido\Services\Health\HealthDatabaseInterface;
use Divido\Services\Health\HealthService;
use Divido\Services\History\HistoryDatabaseInterface;
use Divido\Services\History\HistoryService;
use Divido\Services\LenderCall\LenderCallService;
use Divido\Services\Refund\RefundDatabaseInterface;
use Divido\Services\Refund\RefundService;
use Divido\Services\Signatory\SignatoryDatabaseInterface;
use Divido\Services\Signatory\SignatoryService;
use Divido\Services\Submission\SubmissionDatabaseInterface;
use Divido\Services\Submission\SubmissionService;
use Divido\Services\Tenant\TenantDatabaseInterface;
use Divido\Services\Tenant\TenantService;
use Divido\Traits\RedisAwareTrait;
use Divido\WaterfallApiSdk\Client as WaterfallApiSdk;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ServiceLoader
 *
 * @author Neil McGibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2019, Divido
 */
class ServiceLoader
{
    /**
     * Prepare service singletons as closures inside DI container
     *
     * @param ContainerInterface $container
     */
    public function load(ContainerInterface $container)
    {
        $container['Service.Health'] = function () use ($container) {
            return $this->createService($container, HealthService::class, [
                $container->get('Service.HealthDatabaseInterface'),
            ]);
        };
        $container['Service.HealthDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, HealthDatabaseInterface::class, [
                $container->get('Database.Platform.ReadReplica'),
            ]);
        };

        $container['Service.Tenant'] = function () use ($container) {
            return $this->createService($container, TenantService::class, [
                $container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID),
                $container->get('Service.TenantDatabaseInterface'),
            ]);
        };

        $container['Service.TenantDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, TenantDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Application'] = function () use ($container) {
            return $this->createService($container, ApplicationService::class, [
                $container->get('Service.Tenant'),
                $container->get('Service.ApplicationDatabaseInterface'),
                $container->get('Service.ApplicationSubmission'),
                $container->get('Service.ApplicationCreation'),
                $container->get('Service.HistoryDatabaseInterface'),
                $container->get('Proxy.JsonFuse'),
                $container->get('Proxy.Webhook'),
                $container->get('Proxy.LenderApplicationStatusWkr'),
                $container->get('Cache'),
            ]);
        };

        $container['Service.ApplicationDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, ApplicationDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.ApplicationCreation'] = function () use ($container) {
            return $this->createService($container, ApplicationCreationService::class, [
                $container->get('Database.Platform.ReadReplica'),
                $container->get('Proxy.Calculator'),
                $container->get('Proxy.Validation'),
                $container->get('Sdk.IndexRateApi'),
            ]);
        };

        $container['Service.LenderFee'] = function () use ($container) {
            return $this->createService($container, LenderFeeService::class, [
                $container->get('Sdk.MerchantApi'),
                $container->get('Service.Submission'),
            ]);
        };

        $container['Service.ApplicationSubmission'] = function () use ($container) {
            return $this->createService($container, ApplicationSubmissionService::class, [
                $container->get('Database.Platform.ReadReplica'),
                $container->get('Service.SubmissionDatabaseInterface'),
                $container->get('Proxy.ApplicationSubmissionApi'),
                $container->get('Sdk.MerchantApi'),
                $container->get('Sdk.WaterfallApi'),
            ]);
        };

        $container['Service.ApplicationHistory'] = function () use ($container) {
            return $this->createService($container, HistoryService::class, [
                $container->get('Service.Application'),
                $container->get('Service.HistoryDatabaseInterface'),
            ]);
        };

        $container['Service.HistoryDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, HistoryDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Submission'] = function () use ($container) {
            return $this->createService($container, SubmissionService::class, [
                $container->get('Service.Application'),
                $container->get('Service.SubmissionDatabaseInterface'),
            ]);
        };

        $container['Service.SubmissionDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, SubmissionDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Signatory'] = function () use ($container) {
            return $this->createService($container, SignatoryService::class, [
                $container->get('Service.Application'),
                $container->get('Service.SignatoryDatabaseInterface'),
            ]);
        };

        $container['Service.SignatoryDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, SignatoryDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Activation'] = function () use ($container) {
            return $this->createService($container, ActivationService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Event'),
                $container->get('Service.ActivationDatabaseInterface'),
                $container->get('Proxy.Platform'),
            ]);
        };

        $container['Service.ActivationDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, ActivationDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Cancellation'] = function () use ($container) {
            return $this->createService($container, CancellationService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Event'),
                $container->get('Service.CancellationDatabaseInterface'),
                $container->get('Proxy.Platform'),
            ]);
        };

        $container['Service.CancellationDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, CancellationDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Deposit'] = function ($container) {
            return $this->createService($container, DepositService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Event'),
                $container->get('Service.DepositDatabaseInterface'),
            ]);
        };

        $container['Service.DepositDatabaseInterface'] = function ($container) {
            return $this->createService($container, DepositDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Refund'] = function () use ($container) {
            return $this->createService($container, RefundService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Event'),
                $container->get('Service.RefundDatabaseInterface'),
                $container->get('Proxy.Platform'),
            ]);
        };

        $container['Service.RefundDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, RefundDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.AlternativeOffer'] = function () use ($container) {
            return $this->createService($container, AlternativeOfferService::class, [
                $container->get('Service.Application'),
                $container->get('Service.AlternativeOfferDatabaseInterface'),
            ]);
        };

        $container['Service.AlternativeOfferDatabaseInterface'] = function () use ($container) {
            return $this->createService($container, AlternativeOfferDatabaseInterface::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Service.Event'] = function () use ($container) {
            return $this->createService($container, EventService::class, [
                $container->get('Service.Tenant'),
                $container->get('Sdk.ApplicationApi'),
                $container->get('Helper.SubmissionObjectBuilder')
            ]);
        };

        $container['Service.EventDispatcher'] = function () use ($container) {
            return $this->createService($container, EventDispatcherService::class, [
                $container->get('Event.Cancellation'),
                $container->get('Event.Activation'),
                $container->get('Event.Refund'),
                $container->get('Event.Deposit'),
            ]);
        };

        $container['Service.FormConfiguration'] = function () use ($container) {
            return $this->createService($container, FormConfigurationService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Submission'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi')
            ]);
        };

        $container['Service.LenderCall'] = function () use ($container) {
            return $this->createService($container, LenderCallService::class, [
                $container->get('Service.Application'),
                $container->get('Service.Submission'),
                $container->get('Service.FormConfiguration'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi'),
            ]);
        };

        $container['Event.Cancellation'] = function () use ($container) {
            return $this->createService($container, CancellationEvent::class, [
                $container->get('Service.Application'),
                $container->get('Service.Cancellation'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi'),
            ]);
        };

        $container['Event.Activation'] = function () use ($container) {
            return $this->createService($container, ActivationEvent::class, [
                $container->get('Service.Application'),
                $container->get('Service.Activation'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi'),
            ]);
        };

        $container['Event.Refund'] = function () use ($container) {
            return $this->createService($container, RefundEvent::class, [
                $container->get('Service.Application'),
                $container->get('Service.Refund'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi'),
            ]);
        };

        $container['Event.Deposit'] = function () use ($container) {
            return $this->createService($container, DepositEvent::class, [
                $container->get('Service.Application'),
                $container->get('Service.Deposit'),
                $container->get('Helper.ApplicationObjectBuilder'),
                $container->get('Helper.SubmissionObjectBuilder'),
                $container->get('Sdk.LenderCommunicationApi'),
            ]);
        };

        $container['Proxy.Calculator'] = function () use ($container) {
            $apiUrl = $container['Config']->get('calculation_api.host');
            if (empty($apiUrl)) {
                throw new ConfigPropertyNotFoundException('calculation_api.host');
            }

            return new Calculator(
                $apiUrl
            );
        };

        $container['Proxy.Webhook'] = function () use ($container) {
            $apiUrl = $container['Config']->get('webhook_api.host');
            if (empty($apiUrl)) {
                throw new ConfigPropertyNotFoundException('webhook_api.host');
            }

            return $this->createService($container, Webhook::class, [
                $apiUrl,
                $container->get('Database.Platform.ReadReplica'),
            ]);
        };

        $container['Proxy.JsonFuse'] = function () use ($container) {
            $apiUrl = $container['Config']->get('json_fuse_api.host');
            if (empty($apiUrl)) {
                throw new ConfigPropertyNotFoundException('json_fuse_api.host');
            }

            return $this->createService($container, JsonFuse::class, [
                $apiUrl
            ]);
        };

        $container['Proxy.Validation'] = function () use ($container) {
            $apiUrl = $container['Config']->get('validation_api.host');
            $apiKey = $container['Config']->get('validation_api.key');
            if (empty($apiUrl)) {
                throw new ConfigPropertyNotFoundException('validation_api.host');
            }
            if (empty($apiKey)) {
                throw new ConfigPropertyNotFoundException('validation_api.key');
            }

            return $this->createService($container, Validation::class, [
                $apiUrl, $apiKey
            ]);
        };

        $container['Proxy.LenderApplicationStatusWkr'] = function () use ($container) {

            $client = new SqsClient([
                'region' => getenv('AWS_REGION'),
                'version' => 'latest',
            ]);

            return $this->createService($container, LenderApplicationStatusWkrProxy::class, [
                $client,
                $container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID),
                $container['Config']->get('lender_application_status_wkr.sqs_que')
            ]);
        };

        $container['Proxy.Platform'] = function () use ($container) {
            return $this->createService($container, Platform::class, [
                $container->get('Config')->get('platform.host'),
                $container->get('Config')->get('platform.key'),
            ]);
        };

        $container['Sdk.MerchantApi'] = function (ContainerInterface $container) {
            return new MerchantApiSdk(
                new \Divido\MerchantApiSdk\Wrappers\HttpWrapper(
                    $container->get('Config')->get('merchant_api.host')
                ),
                $container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID)
            );
        };

        $container['Sdk.WaterfallApi'] = function (ContainerInterface $container) {
            $client = new WaterfallApiSdk($container['Config']->get('waterfall_api.host'));
            $client->setTenantId($container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID));

            return $client;
        };

        $container['Sdk.ApplicationApi'] = function (ContainerInterface $container) {
            return new ApplicationApiSdk(
                new \Divido\ApplicationApiSdk\Wrappers\HttpWrapper(
                    $container->get('Config')->get('application_api.host')
                ),
                $container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID)
            );
        };

        $container['Proxy.ApplicationSubmissionApi'] = function (ContainerInterface $container) {
            return $this->createService($container, ApplicationSubmissionApiProxy::class, [
                $container->get('Config')->get('application_submission_api_v2.host'),
                $container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID),
            ]);
        };

        $container['Sdk.LenderCommunicationApi'] = function (ContainerInterface $container) {
            $client = new LenderCommunicationApiSdk($container->get('Config')->get('lender_communication_api.host'));
            $client->setTenantId($container->get('environment')->get(TenantMiddleware::KEY_TENANT_ID));

            return $client;
        };

        $container['Helper.ApplicationObjectBuilder'] = function () use ($container) {
            return $this->createService($container, ApplicationObjectBuilder::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica'),
            ]);
        };

        $container['Helper.SubmissionObjectBuilder'] = function () use ($container) {
            return $this->createService($container, SubmissionObjectBuilder::class, [
                $container->get('Database.Platform.Master'),
                $container->get('Database.Platform.ReadReplica')
            ]);
        };

        $container['Sdk.IndexRateApi'] = function () use ($container) {
            $apiUrl = $container['Config']->get('index_rate_api.host');
            if (!is_string($apiUrl) || empty($apiUrl)) {
                // This was creating issues with single tenant envs where this API was not used (Nordea).
                // For now we decided to comment the exception out.
                // throw new ConfigPropertyNotFoundException('index_rate_api.host');
                $apiUrl = '';
            }

            return new IndexRateApiSdk($apiUrl);
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string $class
     * @param array $args
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createService(ContainerInterface $container, string $class, array $args)
    {
        $service = new $class(...$args);

        if (array_key_exists('Psr\Log\LoggerAwareTrait', $this->class_uses_deep($service))) {
            /** @var LoggerAwareTrait $service */
            $service->setLogger($container->get('Logger'));
        }

        if (array_key_exists('Divido\Traits\RedisAwareTrait', $this->class_uses_deep($service))) {
            /** @var $service RedisAwareTrait */
            $service->setRedis($container->get('Cache'));
        }

        return $service;
    }

    /** Taken from http://php.net/manual/en/function.class-uses.php
     *
     * @param $class
     * @param bool $autoload
     * @return array
     */
    private function class_uses_deep($class, $autoload = true)
    {
        $traits = [];
        // Get all the traits of $class and its parent classes
        do {
            $class_name = is_object($class) ? get_class($class) : $class;
            if (class_exists($class_name, $autoload)) {
                $traits = array_merge(class_uses($class, $autoload), $traits);
            }
        } while ($class = get_parent_class($class));
        // Get traits of all parent traits
        $traits_to_search = $traits;
        while (!empty($traits_to_search)) {
            $new_traits = class_uses(array_pop($traits_to_search), $autoload);
            $traits = array_merge($new_traits, $traits);
            $traits_to_search = array_merge($new_traits, $traits_to_search);
        };

        return array_unique($traits);
    }
}
