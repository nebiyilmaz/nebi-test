<?php
/**
 * Copyright (c) 2018. Divido Financial Services Ltd
 */

namespace Divido\Services\Deposit;

use DateTime;

/**
 * Class Deposit
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020, Divido
 */
class Deposit
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
     * @var string $merchantComment
     */
    private $merchantComment;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var mixed $dataRaw
     */
    private $dataRaw;

    /**
     * @var array $merchantReference
     */
    private $merchantReference;

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
     * @return Deposit
     */
    public function setId(string $id): Deposit
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
     * @return Deposit
     */
    public function setApplicationId(string $applicationId): Deposit
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Deposit
     */
    public function setStatus(?string $status): Deposit
    {
        $this->status = $status;

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
     * @return Deposit
     */
    public function setReference(?string $reference): Deposit
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
     * @return Deposit
     */
    public function setAmount(?int $amount): Deposit
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
     * @return Deposit
     */
    public function setProductData(array $productData): Deposit
    {
        $this->productData = $productData;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantComment(): ?string
    {
        return $this->merchantComment;
    }

    /**
     * @param string $merchantComment
     * @return Deposit
     */
    public function setMerchantComment(?string $merchantComment): Deposit
    {
        $this->merchantComment = $merchantComment;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Deposit
     */
    public function setType(?string $type): Deposit
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataRaw()
    {
        return $this->dataRaw;
    }

    /**
     * @param mixed $dataRaw
     * @return Deposit
     */
    public function setDataRaw($dataRaw): Deposit
    {
        $this->dataRaw = $dataRaw;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): ?string
    {
        return $this->merchantReference;
    }

    /**
     * @param string $merchantReference
     * @return Deposit
     */
    public function setMerchantReference(?string $merchantReference): Deposit
    {
        $this->merchantReference = $merchantReference;

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
     * @return Deposit
     */
    public function setCreatedAt(DateTime $createdAt): Deposit
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
     * @return Deposit
     */
    public function setUpdatedAt(DateTime $updatedAt): Deposit
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
