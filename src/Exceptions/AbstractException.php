<?php

namespace Chvdy\RedisModel\Exceptions;

abstract class AbstractException extends \Exception
{
    protected string $logLevel;

    public function __construct(string $message, int $code, $previous = null) {
        parent::__construct(get_class($this).' '.$message, $code, $previous);

        log_message($this->logLevel, get_class($this) . ": [{$code}] {$message}\n");
    }
}
