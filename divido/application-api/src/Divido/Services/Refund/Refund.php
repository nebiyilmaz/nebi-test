<?php

namespace Divido\Services\Refund;

use DateTime;

/**
 * Class Refund
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Refund
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
     * @return Refund
     */
    public function setId(string $id): Refund
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
     * @return Refund
     */
    public function setApplicationId(string $applicationId): Refund
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
     * @return Refund
     */
    public function setStatus(string $status): Refund
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
     * @return Refund
     */
    public function setAmount(?int $amount): Refund
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
     * @return Refund
     */
    public function setProductData(array $productData): Refund
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
     * @return Refund
     */
    public function setReference(?string $reference): Refund
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
     * @return Refund
     */
    public function setComment(?string $comment): Refund
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
     * @return Refund
     */
    public function setCreatedAt(DateTime $createdAt): Refund
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
     * @return Refund
     */
    public function setUpdatedAt(DateTime $updatedAt): Refund
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
