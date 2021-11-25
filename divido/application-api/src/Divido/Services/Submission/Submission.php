<?php

namespace Divido\Services\Submission;

use DateTime;

/**
 * Class Submission
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Submission
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $applicationId
     */
    private $applicationId;

    /**
     * @var int $order
     */
    private $order;

    /**
     * @var boolean $declineReferred
     */
    private $declineReferred;

    /**
     * @var string $lenderId
     */
    private $lenderId;

    /**
     * @var string $applicationAlternativeOfferId
     */
    private $applicationAlternativeOfferId;

    /**
     * @var string $merchantFinancePlanId
     */
    private $merchantFinancePlanId;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var string $lenderReference
     */
    private $lenderReference;

    /**
     * @var string $lenderLoanReference
     */
    private $lenderLoanReference;

    /**
     * @var string $lenderStatus
     */
    private $lenderStatus;

    /**
     * @var string $lenderCode
     */
    private $lenderCode;

    /**
     * @var object $lenderData
     */
    private $lenderData;

    /**
     * @var DateTime $createdAt
     */
    private $createdAt;

    /**
     * @var DateTime $updatedAt
     */
    private $updatedAt;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Submission
     */
    public function setId(string $id): Submission
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * @param string $applicationId
     * @return Submission
     */
    public function setApplicationId(string $applicationId): Submission
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return Submission
     */
    public function setOrder(int $order): Submission
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeclineReferred(): bool
    {
        return $this->declineReferred;
    }

    /**
     * @param bool $declineReferred
     * @return Submission
     */
    public function setDeclineReferred(bool $declineReferred): Submission
    {
        $this->declineReferred = $declineReferred;

        return $this;
    }

    /**
     * @return string
     */
    public function getLenderId(): string
    {
        return $this->lenderId;
    }

    /**
     * @param string $lenderId
     * @return Submission
     */
    public function setLenderId(string $lenderId): Submission
    {
        $this->lenderId = $lenderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationAlternativeOfferId(): ?string
    {
        return $this->applicationAlternativeOfferId;
    }

    /**
     * @param string $applicationAlternativeOfferId
     * @return Submission
     */
    public function setApplicationAlternativeOfferId(?string $applicationAlternativeOfferId): Submission
    {
        $this->applicationAlternativeOfferId = $applicationAlternativeOfferId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantFinancePlanId(): ?string
    {
        return $this->merchantFinancePlanId;
    }

    /**
     * @param string $merchantFinancePlanId
     * @return Submission
     */
    public function setMerchantFinancePlanId(?string $merchantFinancePlanId): Submission
    {
        $this->merchantFinancePlanId = $merchantFinancePlanId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Submission
     */
    public function setStatus(string $status): Submission
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getLenderReference(): string
    {
        return $this->lenderReference;
    }

    /**
     * @param string $lenderReference
     * @return Submission
     */
    public function setLenderReference(string $lenderReference): Submission
    {
        $this->lenderReference = $lenderReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getLenderLoanReference(): string
    {
        return $this->lenderLoanReference;
    }

    /**
     * @param string $lenderLoanReference
     * @return Submission
     */
    public function setLenderLoanReference(string $lenderLoanReference): Submission
    {
        $this->lenderLoanReference = $lenderLoanReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getLenderStatus(): string
    {
        return $this->lenderStatus;
    }

    /**
     * @param string $lenderStatus
     * @return Submission
     */
    public function setLenderStatus(string $lenderStatus): Submission
    {
        $this->lenderStatus = $lenderStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getLenderCode(): string
    {
        return $this->lenderCode;
    }

    /**
     * @param string $lenderCode
     * @return Submission
     */
    public function setLenderCode(string $lenderCode): Submission
    {
        $this->lenderCode = $lenderCode;

        return $this;
    }

    /**
     * @return object
     */
    public function getLenderData(): object
    {
        return $this->lenderData;
    }

    /**
     * @param object $lenderData
     * @return Submission
     */
    public function setLenderData(object $lenderData): Submission
    {
        $this->lenderData = $lenderData;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Submission
     */
    public function setCreatedAt(DateTime $createdAt): Submission
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     * @return Submission
     */
    public function setUpdatedAt(DateTime $updatedAt): Submission
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
