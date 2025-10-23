<?php

namespace Chvdy\RedisModel;

use CodeIgniter\Entity\Entity;

interface ValidatorInterface
{
    /**
     * Remove protected fields from entity data
     *
     * @param Entity $entity
     * @return array
     */
    public function trimProtectedFields(Entity $entity): array;

    /**
     * Check if validation rules exist for a key
     *
     * @param string $key
     * @return bool
     */
    public function hasRules(string $key): bool;

    /**
     * Check validation for a specific field
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function check(string $key, $value): bool;

    /**
     * Validate entire entity
     *
     * @param Entity $entity
     * @return bool
     */
    public function validate(Entity $entity): bool;
}