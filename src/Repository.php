<?php

namespace Chvdy\RedisModel;

class Repository implements \Chvdy\RedisModel\RepositoryInterface
{
    public function __construct(
        private readonly \Chvdy\RedisModel\Client $client,
        public readonly \Chvdy\RedisModel\KeyManager $keyManager,
        public readonly \Chvdy\RedisModel\RelationManager $relationManager
    ) {
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\NotFound
     */
    public function get(int $id): array
    {
        $data = $this->client->connection()->hGetAll($this->keyManager->getKey($id));

        if (empty($data)) {
            throw new Exceptions\NotFound(
                lang('Exceptions.NotFound', [$id]), Enums\ExceptionCodes::$NotFound);
        }

        return $data;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\CannotGetIds
     */
    public function getAll(): array
    {
        $ids = $this->keyManager->getIds();
        $collection = [];
        foreach ($ids as $id) {
            $result = $this->client->connection()->hGetAll($id);

            if ($result === false) {
                continue;
            }
            $collection[] = $result;
        }

        return $collection;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     */
    public function update(int $id, array $data): bool
    {
        return (bool)$this->client->connection()->hMset($this->keyManager->getKey($id), $data);
    }

    /**
     * @return int return id of created object
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\WrongIncrementId
     * @throws \Chvdy\RedisModel\Exceptions\CannotSaveHashCollectionFields
     */
    public function insert(array $data): int
    {
        $incr = $this->keyManager->getIncrement(true);
        if ($incr === 0) {
            throw new Exceptions\WrongIncrementId(
                lang('Exceptions.WrongIncrementId'), Enums\ExceptionCodes::$WrongIncrementId);
        }
        $data['id'] = $incr;
        $result = $this->client->connection()->hMSet($this->keyManager->getKey($incr), $data);

        if (!$result) {
            throw new Exceptions\CannotSaveHashCollectionFields(
                lang('Exceptions.CannotSaveHashCollectionFields', [$incr]),
                Enums\ExceptionCodes::$CannotSaveHashCollectionFields);
        }

        return $incr;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\NotFound
     * @throws \Chvdy\RedisModel\Exceptions\CannotRemove
     */
    public function delete(int $id): int
    {
        $this->get($id);
        $result = $this->client->connection()->del($this->keyManager->getKey($id));

        if ($result === false) {
            throw new Exceptions\CannotRemove(
                lang('Exceptions.CannotRemove', [$id]),
                Enums\ExceptionCodes::$CannotRemove);
        }

        if ($result > 0) {
            $this->relationManager->removeRelations($id);
        }

        return $result;
    }

}
