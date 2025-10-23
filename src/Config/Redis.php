<?php

declare(strict_types=1);

namespace Chvdy\RedisModel\Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    public string $host = '127.0.0.1';
    public string $username = 'default';
    public string $password = '';
    public int $port = 6379;
    public int $timeout = 2;
    public int $retries = 3;
}
