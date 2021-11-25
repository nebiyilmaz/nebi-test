<?php

namespace Divido\Services\Health;

use DateTime;

/**
 * Class Health
 *
 * @copyright (c) 2018, Divido
 */
class Health
{
    /**
     * @var DateTime $checkedAt
     */
    private $checkedAt;

    /**
     * @return DateTime
     */
    public function getCheckedAt(): DateTime
    {
        return $this->checkedAt;
    }

    /**
     * @param DateTime $checkedAt
     * @return Health
     */
    public function setCheckedAt(DateTime $checkedAt): Health
    {
        $this->checkedAt = $checkedAt;

        return $this;
    }
}
