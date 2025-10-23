<?php

namespace Chvdy\RedisModel\Exceptions;

class Validation extends \Chvdy\RedisModel\Exceptions\AbstractException
{
    protected string $logLevel = \Psr\Log\LogLevel::INFO;
    protected array $errors = [];

    public function __construct($message, $code, array $validationErrors = [], $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $validationErrors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
