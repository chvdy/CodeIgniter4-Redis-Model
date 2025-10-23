<?php

namespace Chvdy\RedisModel\Exceptions;

class CannotRemove extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
