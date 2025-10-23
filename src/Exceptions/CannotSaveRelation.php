<?php

namespace Chvdy\RedisModel\Exceptions;

class CannotSaveRelation extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::EMERGENCY;
}
