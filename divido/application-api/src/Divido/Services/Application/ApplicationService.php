<?php

namespace Divido\Services\Application;

use Divido\ApiExceptions\ApplicationInputInvalidException;
use Divido\ApiExceptions\ApplicationSubmissionErrorException;
use Divido\ApiExceptions\ResourceNotFoundException;
use Divido\ApiExceptions\TenantMissingOrInvalidException;
use Divido\ApiExceptions\UnauthorizedException;
use Divido\Cache\CacheInterface;
use Divido\Exceptions\ApplicationApiException;
use Divido\Helpers\FormatFormData;
use Divido\Helpers\MapFormDataToApplicants;
use Divido\Helpers\Paginator\PaginatorHelper;
use Divido\MerchantApi\MerchantApiClientException;
use Divido\Proxies\JsonFuse;
use Divido\Proxies\LenderApplicationStatusWkrProxy;
use Divido\Proxies\Webhook;
use Divido\Services\History\History;
use Divido\Services\History\HistoryDatabaseInterface;
use Divido\Services\Submission\Submission;
use Divido\Services\Tenant\Tenant;
use Divido\Services\Tenant\TenantService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;

/**
 * Class ApplicationService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class ApplicationService
{
    use LoggerAwareTrait;

    /** @var ApplicationDatabaseInterface $applicationDatabaseInterface */
    private $applicationDatabaseInterface;

    /** @var ApplicationSubmissionService $applicationSubmissionService */
    private $applicationSubmissionService;

    /** @var ApplicationCreationService $applicationCreationService */
    private $applicationCreationService;

    /** @var HistoryDatabaseInterface $historyDatabaseInterface */
    private $historyDatabaseInterface;

    /** @var JsonFuse $jsonFuseProxy */
    private $jsonFuseProxy;

    /** @var Webhook $webhookApiProxy */
    private $webhookApiProxy;

    /** @var LenderApplicationStatusWkrProxy $lenderApplicationStatusWkrProxy */
    private $lenderApplicationStatusWkrProxy;

    /** @var Tenant $tenant */
    private $tenant;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * ApplicationService constructor.
     * @param TenantService $tenantService
     * @param ApplicationDatabaseInterface $applicationDatabaseInterface
     * @param ApplicationSubmissionService $applicationSubmissionService
     * @param ApplicationCreationService $applicationCreationService
     * @param HistoryDatabaseInterface $historyDatabaseInterface
     * @param JsonFuse $jsonFuseProxy
     * @param Webhook $webhookApiProxy
     * @param LenderApplicationStatusWkrProxy $lenderApplicationStatusWkrProxy
     * @param CacheInterface $cache
     * @throws TenantMissingOrInvalidException
     */
    function __construct(TenantService $tenantService, ApplicationDatabaseInterface $applicationDatabaseInterface,
                         ApplicationSubmissionService $applicationSubmissionService, ApplicationCreationService $applicationCreationService,
                         HistoryDatabaseInterface $historyDatabaseInterface, JsonFuse $jsonFuseProxy, Webhook $webhookApiProxy,
                         LenderApplicationStatusWkrProxy $lenderApplicationStatusWkrProxy, CacheInterface $cache)
    {
        $this->applicationDatabaseInterface = $applicationDatabaseInterface;
        $this->applicationSubmissionService = $applicationSubmissionService;
        $this->applicationCreationService = $applicationCreationService;
        $this->historyDatabaseInterface = $historyDatabaseInterface;
        $this->jsonFuseProxy = $jsonFuseProxy;
        $this->webhookApiProxy = $webhookApiProxy;
        $this->lenderApplicationStatusWkrProxy = $lenderApplicationStatusWkrProxy;
        $this->cache = $cache;

        $this->tenant = $tenantService->getOne();
    }

    /**
     * @param Application $model
     * @return Application
     * @throws ApplicationInputInvalidException
     * @throws GuzzleException
     * @throws MerchantApiClientException
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\IndexRateSdk\Exception
     */
    public function create(Application $model)
    {
        $id = Uuid::uuid4()->toString();
        $model->setTenantId($this->tenant->getId());
        $model->setId($id);
        $model->setToken($this->applicationCreationService->generateToken());

        $purchasePrice = $this->applicationCreationService->calculatePurchasePrice($model);
        $model->setPurchasePrice($purchasePrice);

        $model = $this->applicationCreationService->validateProductData($model);

        $environment = $this->applicationCreationService->validateTenant($model);
        $country = $this->applicationCreationService->validateCountryCode($model, $environment);

        if (!$model->getCurrencyCode()) {
            $model->setCurrencyCode($country->currency_code);
        } else {
            $this->applicationCreationService->validateCurrencyCode($model, $country);
        }

        if (!$model->getLanguageCode()) {
            $model->setLanguageCode(current($country->languages));
        } else {
            $this->applicationCreationService->validateLanguageCode($model, $country);
        }

        $merchant = $this->applicationCreationService->validateMerchant($model);
        $model->setMerchantId($merchant->id);
        $model->setBranchId($merchant->branch_id);
        $channel = $this->applicationCreationService->validateChannel($model);
        $depositStatus = $this->applicationCreationService->getDepositStatus($model, $merchant, $channel);

        $this->applicationCreationService->validateMerchantApiKey($model);
        $merchantUser = $this->applicationCreationService->validateMerchantUser($model);

        $model->setDepositStatus($depositStatus);

        $financeOption = $this->applicationCreationService->validateFinanceOption($model);

        if ($model->getDepositAmount() == 0) {
            $model->setDepositStatus("NO-DEPOSIT");
        }

        $tenantSettings = $this->tenant->getSettings();

        if (!empty($tenantSettings['pii']['application_creation']['remove_pii_from_merchant_calls']) &&
            $tenantSettings['pii']['application_creation']['remove_pii_from_merchant_calls']) {

            $applicantObfuscation = new ApplicantObfuscation($tenantSettings['pii']['application_creation']['fields'] ?? []);
            $model = $applicantObfuscation->obfuscate($model);
        }

        $formData = $this->applicationCreationService->populateFormData($model);

        if (empty((array) $model->getApplicants())) {
            $applicants = (new MapFormDataToApplicants())->getApplicants($formData, $model->getApplicants());

            try {
                $start = microtime(true);

                $applicants = $this->jsonFuseProxy->fuse(null, $applicants);
                $model->setApplicants($applicants);

                $end = microtime(true);
                $this->logMicrotime(($end - $start), __METHOD__, ['action' => 'fuse from form data', 'application_id' => $model->getId()]);
            } catch (ApplicationApiException $e) {

            }

            $formData = (new FormatFormData())->formatData($formData);
            $model->setFormData($formData);
        } else {
            $newApplicants = $model->getApplicants();
            if (empty((array) $newApplicants)) {
                $newApplicants = (object) ['value' => [(object) ['value' => (object) []]]];
            }

            try {
                $start = microtime(true);

                $applicants = $this->jsonFuseProxy->fuse($model->getApplicants(), $newApplicants);
                $model->setApplicants($applicants);

                $end = microtime(true);
                $this->logMicrotime(($end - $start), __METHOD__, ['action' => 'fuse applicants', 'application_id' => $model->getId()]);
            } catch (ApplicationApiException $e) {

            }
        }

        /**
         * Todo:
         * We have to refactor this and do the calculations after we have created the submission so we know the primary lender
         */
        $financeSettings = $this->applicationCreationService->generateFinanceSettings($model, $financeOption);

        $model->setStatus('PROPOSAL')
            ->setFinalised(($model->isFinalisationRequired()) ? true : false)
            ->setLenderFee(0)
            ->setCommission(0)
            ->setPartnerCommission(0)
            ->setFinanceSettings(json_decode(json_encode($financeSettings)));

        $terms = $this->applicationCreationService->getFinanceTerms($model);
        $model->setTerms($terms);

        $this->applicationDatabaseInterface->createNewApplicationFromModel($model);

        $this->setPinCodeInCache($model, $model->getPinCode());

        $historyModel = (new History())
            ->setApplicationId($id)
            ->setType('status')
            ->setStatus('PROPOSAL');

        if (!empty($merchantUser->id)) {
            $historyModel->setUser($merchantUser->id);
            $historyModel->setText('Proposal created by ' . $merchantUser->name);
        }
        $this->historyDatabaseInterface->createNewHistoryFromModel($historyModel);

        $model = $this->applicationSubmissionService->createSubmissions($model);

        $model = $this->update($model);

        $this->webhookApiProxy->send('proposal-created', $model);

        return $model;
    }

    /**
     * @param Application $application
     * @param string $pinCode
     */
    private function setPinCodeInCache(Application $application, string $pinCode)
    {
        if ($application->getTenantId() !== 'nordea') {
            // Only need to set pin codes for Nordea tenant
            return;
        }

        // Get user's phone number
        $phoneNumber = $this->sanitisePhoneNumber($application);

        // Get user's email address
        $mailAddress = $this->sanitiseEmailAddress($application);

        $this->setPinCodeItemCache($application, $phoneNumber, 'phone_number', $pinCode);
        $this->setPinCodeItemCache($application, $mailAddress, 'email', $pinCode);
    }

    /**
     * Sanitises the .phoneNumber property in `form_data` using the same method as
     * nordea-active-link-api
     *
     * @param Application $application
     * @return string
     */
    private function sanitisePhoneNumber(Application $application)
    {
        if (!property_exists($application->getFormData(), "phoneNumber") || empty($application->getFormData()->phoneNumber)) {
            return "";
        }

        return preg_replace('/[^+0-9]/', '', $application->getFormData()->phoneNumber);
    }

    /**
     * Sanitises the .email property in `form_data`
     *
     * @param Application $application
     * @return string
     */
    private function sanitiseEmailAddress(Application $application)
    {
        if (!property_exists($application->getFormData(), "email") || empty($application->getFormData()->email)) {
            return "";
        }

        return strtolower($application->getFormData()->email);
    }

    /**
     * @param Application $application
     * @param string $value
     * @param string $key
     * @param string $pinCode
     */
    private function setPinCodeItemCache(Application $application, string $value, string $key, string $pinCode)
    {
        if (empty($value)) {
            $this->logger->warning('will not set redis entry on empty identifier', [
                'id' => $application->getId(),
                'key' => $key,
            ]);

            return;
        }

        $this->logger->debug("inserting pin code to cache", [
            "application_id" => $application->getId(),
            "type" => $key,
        ]);

        $key = vsprintf("%s:%s:%s", [$application->getTenantId(), $key, $value]);
        $this->cache->rPush($key, json_encode(['id' => $application->getId(),  'pin' => $pinCode,]));
        $this->cache->setTimeout($key, 86400*60); // 2 months. Ish.

        // Let's also set our hashed version now, which makes things safer upstream
        $hashedKey = vsprintf("%s:hash:%s", [$application->getTenantId(), md5($value)]);
        $this->cache->rPush($hashedKey, json_encode(['id' => $application->getId(), 'pin' => $pinCode,]));
        $this->cache->setTimeout($hashedKey, 86400*60); // 2 months. Ish.
    }

    /**
     * @param Application $application
     * @return bool
     * @throws ApplicationSubmissionErrorException
     * @throws ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     */
    public function submit(Application $application)
    {
        if ($application->getStatus() != 'PROPOSAL') {
            throw new ApplicationSubmissionErrorException('Invalid status, current status: ' . $application->getStatus());
        }

        $application->setStatus('AWAITING-SUBMISSION');
        $this->update($application);

        return $this->applicationSubmissionService->submitApplicationToApplicationSubmissionApi($application);
    }

    /**
     * @param Application $model
     * @param bool $useReadReplica
     * @return Application
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function getOne(Application $model, $useReadReplica = true)
    {
        $model = $this->applicationDatabaseInterface->getApplicationFromModel($this->tenant, $model, $useReadReplica);

        if ($model->getTenantId() != $this->tenant->getId()) {
            throw new UnauthorizedException();
        }

        return $model;
    }

    /**
     * @param PaginatorHelper $paginator
     * @return array
     * @throws Exception
     */
    public function getAll(PaginatorHelper $paginator)
    {
        return $this->applicationDatabaseInterface->getAllApplications($this->tenant, $paginator);
    }

    /**
     * @param Application $model
     * @return Application
     * @throws ResourceNotFoundException
     * @throws GuzzleException
     */
    public function update(Application $model)
    {
        $this->applicationCreationService->populateFormData($model);

        $this->applicationDatabaseInterface->updateApplicationFromModel($model);

        return $this->applicationDatabaseInterface->getApplicationFromModel($this->tenant, $model, false);
    }

    /**
     * @param Application $model
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function delete(Application $model)
    {
        $this->applicationDatabaseInterface->getApplicationFromModel($this->tenant, $model);

        return $this->applicationDatabaseInterface->deleteApplicationFromModel($model);
    }

    /**
     * @param Application $application
     * @param History $history
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function updateStatus(Application $application, History $history)
    {
        if ($application->getStatus() != $history->getStatus()) {
            $application->setStatus($history->getStatus());
            if ($history->getStatus() === 'DEPOSIT-PAID') {
                $application->setDepositStatus('PAID');
            }
            $this->applicationDatabaseInterface->updateApplicationFromModel($application);

            // Send Webhook
            $this->webhookApiProxy->send('application-status-update', $application);

            return true;
        }

        return false;
    }

    /**
     * @param $application
     * @param $status
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function createApplicationStatusRequest($application, $status)
    {
        if (!empty($status) && $application->getStatus() != $status) {
            $history = (new History())
                ->setStatus($status)
                ->setApplicationId($application->getId())
                ->setType('status');

            $history = $this->historyDatabaseInterface->createNewHistoryFromModel($history);

            return $this->updateStatus($application, $history);
        }

        return false;
    }

    /**
     * @param Application $application
     * @param Submission $submission
     * @return void
     * @throws ResourceNotFoundException
     */
    public function submissionStatusChangeListener(Application $application, Submission $submission)
    {
        $applicationStatusWasUpdated = false;

        /*
         * TODO:
         * We need to think about how we would like to handle application status changes
         */
        $applicationStatusWasUpdated = $this->createApplicationStatusRequest($application, $submission->getStatus());

        // Add event to SQS for automatic polling of status changes if the status did change
        if ($applicationStatusWasUpdated) {
            $this->lenderApplicationStatusWkrProxy->statusChange($submission->getId(), $submission->getStatus());
        }

    }

    private function logMicrotime($time, $method, $data)
    {
        $this->logger->info('microtime', [
            'class' => __CLASS__,
            'method' => $method,
            'time' => $time,
            'data' => $data,
        ]);
    }
}
