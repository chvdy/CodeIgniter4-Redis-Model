<?php

namespace Chvdy\RedisModel\Exceptions;

class CannotRemoveFromSet extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
