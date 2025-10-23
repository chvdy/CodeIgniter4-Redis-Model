<?php

namespace Chvdy\RedisModel\Exceptions;

class HasNotRelated extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::INFO;
}
