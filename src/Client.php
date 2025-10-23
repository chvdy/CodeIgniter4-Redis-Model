<?php

namespace Chvdy\RedisModel;

class Client implements ClientInterface
{
    protected ?\Redis $client = null;
    private ?array $config = null;
    private string $database;
    private int $retries = 0;

    public function __construct()
    {
        $config = config(\App\Config\Redis::class);
        $this->config = [
            'host' => $config->host,
            'port' => $config->port,
            'connectTimeout' => $config->timeout,
            'auth' => [$config->username, $config->password],
        ];
        $this->database = (ENVIRONMENT === 'development') ?
            Enums\RedisDatabase::DEVELOPMENT :
            Enums\RedisDatabase::PRODUCTION;
        $this->retries = $config->retries;
    }

    public function connection(): \Redis
    {
        if ($this->status()) {
            return $this->client;
        }

        for ($i = 0; $i < $this->retries; $i++) {
            try {
                $this->client = new \Redis($this->config);
                $this->client->select($this->database);
            } catch (\RedisException $e) {
                log_message('error', lang('RedisModel.ConnectionFailed', [$e->getMessage()]));

                if ($i === ($this->retries -1)) {
                    log_message('error', lang('Exceptions.ConnectionFailed', [$this->retries, $e->getMessage()]));

                    throw new Exceptions\ConnectionFailed(
                        lang('Exceptions.ConnectionFailed', [$e->getMessage()]), Enums\ExceptionCodes::$ConnectionFailed);
                }
            }
        }

        return $this->client;
    }

    public function status(): bool
    {
        try {
            return $this->client !== null &&
                $this->client->isConnected() &&
                $this->client->ping();
        } catch (\RedisException $e) {
            return false;
        }
    }
}
