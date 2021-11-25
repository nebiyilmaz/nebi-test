<?php

namespace Divido\Services\Signatory;

use DateTime;

/**
 * Class Signatory
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Signatory
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
     * @var string $firstName
     */
    private $firstName;

    /**
     * @var string $lastName
     */
    private $lastName;

    /**
     * @var string $emailAddress
     */
    private $emailAddress;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var DateTime $dateOfBirth
     */
    private $dateOfBirth;

    /**
     * @var string $lenderReference
     */
    private $lenderReference;

    /**
     * @var boolean $hostedSigning
     */
    private $hostedSigning;

    /**
     * @var object $dataRaw
     */
    private $dataRaw;

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
     * @return Signatory
     */
    public function setId(string $id): Signatory
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
     * @return Signatory
     */
    public function setApplicationId(string $applicationId): Signatory
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Signatory
     */
    public function setFirstName(string $firstName): Signatory
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Signatory
     */
    public function setLastName(string $lastName): Signatory
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return Signatory
     */
    public function setEmailAddress(string $emailAddress): Signatory
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Signatory
     */
    public function setTitle(string $title): Signatory
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateOfBirth(): DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     * @param DateTime $dateOfBirth
     * @return Signatory
     */
    public function setDateOfBirth(DateTime $dateOfBirth): Signatory
    {
        $this->dateOfBirth = $dateOfBirth;

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
     * @return Signatory
     */
    public function setLenderReference(string $lenderReference): Signatory
    {
        $this->lenderReference = $lenderReference;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHostedSigning(): bool
    {
        return $this->hostedSigning;
    }

    /**
     * @param bool $hostedSigning
     * @return Signatory
     */
    public function setHostedSigning(bool $hostedSigning): Signatory
    {
        $this->hostedSigning = $hostedSigning;

        return $this;
    }

    /**
     * @return object
     */
    public function getDataRaw(): object
    {
        return $this->dataRaw;
    }

    /**
     * @param object $dataRaw
     * @return Signatory
     */
    public function setDataRaw(object $dataRaw): Signatory
    {
        $this->dataRaw = $dataRaw;

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
     * @return Signatory
     */
    public function setCreatedAt(DateTime $createdAt): Signatory
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
     * @return Signatory
     */
    public function setUpdatedAt(DateTime $updatedAt): Signatory
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
