<?php

namespace Divido\Services\History;

use DateTime;

/**
 * Class History
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class History
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
     * @var string $type
     */
    private $type;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var string $user
     */
    private $user;

    /**
     * @var string $subject
     */
    private $subject;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var boolean $internal
     */
    private $internal;

    /**
     * @var DateTime $date
     */
    private $date;

    /**
     * @var string $ipAddress
     */
    private $ipAddress;

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
     * @return History
     */
    public function setId(string $id): History
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
     * @return History
     */
    public function setApplicationId(string $applicationId): History
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return History
     */
    public function setType(string $type): History
    {
        $this->type = $type;

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
     * @return History
     */
    public function setStatus(string $status): History
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): ?string
    {
        return $this->user ?? "";
    }

    /**
     * @param string $user
     * @return History
     */
    public function setUser(?string $user): History
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject ?? "";
    }

    /**
     * @param string $subject
     * @return History
     */
    public function setSubject(string $subject): History
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text ?? "";
    }

    /**
     * @param string $text
     * @return History
     */
    public function setText(string $text): History
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return ($this->internal) ? true : false;
    }

    /**
     * @param bool $internal
     * @return History
     */
    public function setInternal(bool $internal): History
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        if (!$this->date) {
            $this->date = New DateTime();
        }

        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return History
     */
    public function setDate(DateTime $date): History
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress ?? "";
    }

    /**
     * @param string $ipAddress
     * @return History
     */
    public function setIpAddress(?string $ipAddress): History
    {
        $this->ipAddress = $ipAddress;

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
     * @return History
     */
    public function setCreatedAt(DateTime $createdAt): History
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
     * @return History
     */
    public function setUpdatedAt(DateTime $updatedAt): History
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
