<?php

declare(strict_types=1);

namespace Chvdy\RedisModel\Exceptions;

class ConnectionFailed extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
