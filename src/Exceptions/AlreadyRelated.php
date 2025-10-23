<?php

namespace Chvdy\RedisModel\Exceptions;

class AlreadyRelated extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::INFO;
}
