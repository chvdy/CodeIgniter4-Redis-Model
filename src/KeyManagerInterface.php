<?php

namespace Chvdy\RedisModel;

interface KeyManagerInterface
{
    /**
     * @param int|null $id
     */
    public function getKey($id = null): string;

    public function getRelationKey(string $relation, int $id_parent): string;

    /**
     * @param bool $iterate true to increase counter before returned
     * @return int return current value of primary key
     */
    public function getIncrement(bool $iterate = false): int;
}