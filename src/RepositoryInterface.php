<?php

namespace Chvdy\RedisModel;

interface RepositoryInterface
{
    public function get(int $id): array;

    public function getAll(): array;

    public function insert(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): int;
}
