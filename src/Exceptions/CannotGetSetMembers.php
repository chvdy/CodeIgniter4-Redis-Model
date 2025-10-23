<?php

namespace Chvdy\RedisModel\Exceptions;

class CannotGetSetMembers extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
