<?php

namespace Chvdy\RedisModel\Exceptions;

class CannotGetIds extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
