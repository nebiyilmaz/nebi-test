<?php
/**
 * Copyright (c) 2018. Divido Financial Services Ltd
 */

namespace Divido\Services\Activation;

use DateTime;

/**
 * Class Activation
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Activation
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
     * @var string $status
     */
    private $status;

    /**
     * @var string $reference
     */
    private $reference;

    /**
     * @var int $amount
     */
    private $amount;

    /**
     * @var array $productData
     */
    private $productData;

    /**
     * @var string $deliveryMethod
     */
    private $deliveryMethod;

    /**
     * @var string $trackingNumber
     */
    private $trackingNumber;

    /**
     * @var string $comment
     */
    private $comment;

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
     * @return Activation
     */
    public function setId(string $id): Activation
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
     * @return Activation
     */
    public function setApplicationId(string $applicationId): Activation
    {
        $this->applicationId = $applicationId;

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
     * @return Activation
     */
    public function setStatus(string $status): Activation
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param ?string $reference
     * @return Activation
     */
    public function setReference(?string $reference): Activation
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Activation
     */
    public function setAmount(?int $amount): Activation
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductData(): array
    {
        return $this->productData;
    }

    /**
     * @param array $productData
     * @return Activation
     */
    public function setProductData(array $productData): Activation
    {
        $this->productData = $productData;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryMethod(): ?string
    {
        return $this->deliveryMethod;
    }

    /**
     * @param string $deliveryMethod
     * @return Activation
     */
    public function setDeliveryMethod(?string $deliveryMethod): Activation
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     * @return Activation
     */
    public function setTrackingNumber(?string $trackingNumber): Activation
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param ?string $comment
     * @return Activation
     */
    public function setComment(?string $comment): Activation
    {
        $this->comment = $comment;

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
     * @return Activation
     */
    public function setCreatedAt(DateTime $createdAt): Activation
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
     * @return Activation
     */
    public function setUpdatedAt(DateTime $updatedAt): Activation
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
