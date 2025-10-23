<?php

namespace Chvdy\RedisModel\Exceptions;

class WrongIncrementId extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
