<?php

namespace Chvdy\RedisModel\Exceptions;

class RelatedObjectNotFound extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::INFO;
}
