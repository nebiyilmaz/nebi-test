<?php

namespace Divido\Services\Cancellation;

use DateTime;

/**
 * Class Cancellation
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Cancellation
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
     * @var int $amount
     */
    private $amount;

    /**
     * @var array $productData
     */
    private $productData;

    /**
     * @var string $reference
     */
    private $reference;

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
     * @return Cancellation
     */
    public function setId(string $id): Cancellation
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
     * @return Cancellation
     */
    public function setApplicationId(string $applicationId): Cancellation
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
     * @return Cancellation
     */
    public function setStatus(string $status): Cancellation
    {
        $this->status = $status;

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
     * @return Cancellation
     */
    public function setAmount(?int $amount): Cancellation
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
     * @return Cancellation
     */
    public function setProductData(array $productData): Cancellation
    {
        $this->productData = $productData;

        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return Cancellation
     */
    public function setReference(?string $reference): Cancellation
    {
        $this->reference = $reference;

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
     * @return Cancellation
     */
    public function setComment(?string $comment): Cancellation
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
     * @return Cancellation
     */
    public function setCreatedAt(DateTime $createdAt): Cancellation
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
     * @return Cancellation
     */
    public function setUpdatedAt(DateTime $updatedAt): Cancellation
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
