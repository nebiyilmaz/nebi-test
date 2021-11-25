<?php

namespace Divido\Services\Tenant;

use DateTime;

/**
 * Class PlatformEnvironment
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class Tenant
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $settings = [];

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
     * @return Tenant
     */
    public function setId(string $id): Tenant
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Tenant
     */
    public function setName(string $name): Tenant
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return Tenant
     */
    public function setSettings(array $settings): Tenant
    {
        if (is_null($settings)) {
            $settings = [];
        }

        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }
        $this->settings = $settings;

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
     * @return Tenant
     */
    public function setCreatedAt(DateTime $createdAt): Tenant
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
     * @return Tenant
     */
    public function setUpdatedAt(DateTime $updatedAt): Tenant
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
