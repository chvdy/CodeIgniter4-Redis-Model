<?php

namespace Chvdy\RedisModel\Exceptions;

class ParentObjectNotFound extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::INFO;
}
