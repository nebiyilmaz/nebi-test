<?php
/**
 * Copyright (c) 2018. Divido Financial Services Ltd
 */

namespace Divido\Services\AlternativeOffer;

use DateTime;

/**
 * Class AlternativeOffer
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class AlternativeOffer
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
     * @var string $lenderId
     */
    private $lenderId;

    /**
     * @var object $data
     */
    private $data;

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
     * @return AlternativeOffer
     */
    public function setId(string $id): AlternativeOffer
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
     * @return AlternativeOffer
     */
    public function setApplicationId(string $applicationId): AlternativeOffer
    {
        $this->applicationId = $applicationId;

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
     * @return AlternativeOffer
     */
    public function setLenderId(string $lenderId): AlternativeOffer
    {
        $this->lenderId = $lenderId;

        return $this;
    }

    /**
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }

    /**
     * @param object $data
     * @return AlternativeOffer
     */
    public function setData(object $data): AlternativeOffer
    {
        $this->data = $data;

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
     * @return AlternativeOffer
     */
    public function setCreatedAt(DateTime $createdAt): AlternativeOffer
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
     * @return AlternativeOffer
     */
    public function setUpdatedAt(DateTime $updatedAt): AlternativeOffer
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
