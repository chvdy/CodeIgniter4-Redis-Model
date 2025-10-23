<?php

namespace Chvdy\RedisModel;

use Redis;

interface ClientInterface
{
    /**
     * @return Redis
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     */
    public function connection(): Redis;

    /**
     * @return bool
     */
    public function status(): bool;
}