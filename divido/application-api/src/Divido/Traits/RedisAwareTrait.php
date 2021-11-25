<?php

namespace Divido\Traits;

use Divido\Cache\RedisAdapter;

/**
 * Trait RedisAwareTrait
 */
trait RedisAwareTrait
{
    /**
     * The RedisAdapter instance.
     *
     * @var RedisAdapter
     */
    protected $redis;

    /**
     * Sets redis.
     *
     * @param RedisAdapter $redis
     */
    public function setRedis(RedisAdapter $redis)
    {
        $this->redis = $redis;
    }
}
