<?php

namespace Divido\Services\LenderFee;

use Divido\MerchantApiSdk\Client as MerchantApiSdk;
use Divido\MerchantApiSdk\Models\FinancePlan\FinancePlan;
use Divido\Services\Application\Application;
use Divido\Services\Submission\Submission;
use Divido\Services\Submission\SubmissionService;
use Psr\Log\LoggerAwareTrait;

/**
 * Class LenderFeeService
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2021, Divido
 */
class LenderFeeService
{
    use LoggerAwareTrait;

    /** @var MerchantApiSdk $merchantApiSdk */
    private $merchantApiSdk;

    /** @var SubmissionService $submissionService */
    private $submissionService;

    /** @var FinancePlan|null $financePlan */
    private ?FinancePlan $financePlan = null;

    /**
     * MerchantPortalService constructor.
     * @param MerchantApiSdk $merchantApiSdk
     */
    function __construct(
        MerchantApiSdk $merchantApiSdk,
        SubmissionService $submissionService
    )
    {
        $this->merchantApiSdk = $merchantApiSdk;
        $this->submissionService = $submissionService;
    }

    /**
     * @param Application $model
     * @return string
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\MerchantApiSdk\Exceptions\MerchantApiSdkException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateLenderFee(Application $model)
    {
        $financePlan = $this->getFinancePlanFromSubmissionId($model->getApplicationSubmissionId());

        $lenderFee = ($financePlan->getLenderFeePercentage()) * $model->getCreditAmount();
        return ($lenderFee < $financePlan->getLenderFeeMinimumAmount()) ? $financePlan->getLenderFeeMinimumAmount() : $lenderFee;
    }

    /**
     * @param Application $model
     * @return string
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\MerchantApiSdk\Exceptions\MerchantApiSdkException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateCommission(Application $model)
    {
        $financePlan = $this->getFinancePlanFromSubmissionId($model->getApplicationSubmissionId());
        return ($financePlan->getCommissionPercentage()) * $model->getCreditAmount();
    }

    /**
     * @param Application $model
     * @return string
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\MerchantApiSdk\Exceptions\MerchantApiSdkException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculatePartnerCommission(Application $model)
    {
        $financePlan = $this->getFinancePlanFromSubmissionId($model->getApplicationSubmissionId());
        return ($financePlan->getPartnerCommissionPercentage()) * $model->getCreditAmount();
    }

    /**
     * @param $submissionId
     * @return FinancePlan|null
     * @throws \Divido\ApiExceptions\ResourceNotFoundException
     * @throws \Divido\ApiExceptions\UnauthorizedException
     * @throws \Divido\ApiExceptions\UpstreamServiceBadResponseException
     * @throws \Divido\MerchantApiSdk\Exceptions\MerchantApiSdkException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getFinancePlanFromSubmissionId($submissionId)
    {
        if (!$this->financePlan) {
            $submission = $this->submissionService->getOne((new Submission())->setId($submissionId));
            $this->financePlan = $this->merchantApiSdk->getOneFinancePlan($submission->getMerchantFinancePlanId());
        }

        return $this->financePlan;
    }
}
