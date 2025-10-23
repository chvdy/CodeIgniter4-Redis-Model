<?php

namespace Chvdy\RedisModel;

class KeyManager implements KeyManagerInterface
{
    private string $key;
    private array $relationKeys;

    public function __construct(
        private readonly \Chvdy\RedisModel\Client $client,
        private readonly string $entity,
        private readonly array $has_many,
    ) {
    }

    public function getKey($id = null): string
    {
        if (empty($this->key)) {
            $this->key = strtolower(str_replace('\\', '.', $this->entity));
        }

        if ($id !== null) {
            return $this->key.':'.$id;
        }

        return $this->key;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\CannotGetIds
     */
    public function getIds(): array
    {
        $result = $this->client->connection()->keys($this->getKey('*'));

        if ($result === false) {
            throw new Exceptions\CannotGetIds(
                lang('Exceptions.CannotGetIds'), Enums\ExceptionCodes::$CannotGetIds);
        }

        return $result;
    }

    public function getRelationKey(string $relation, int $id_parent): string
    {
        if (isset($this->relationKeys[$relation])) {
            return $this->relationKeys[$relation] . ':' . $id_parent;
        }

        $this->relationKeys[$relation] = strtolower(
            str_replace('\\', '.', $this->entity.'__'.$this->has_many[$relation])
        );

        return $this->relationKeys[$relation] . ':' . $id_parent;
    }

    /**
     * @param bool $iterate true to increase counter before returned
     * @return int return current value of primary key
     */
    public function getIncrement(bool $iterate = false): int
    {
        $key = $this->getKey();

        if (!$this->client->connection()->exists($key)) {
            $this->client->connection()->set($key, 0);
        }

        if ($iterate) {
            return (int)$this->client->connection()->incr($key);
        }

        return (int)$this->client->connection()->get($key);
    }
}
