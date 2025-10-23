<?php

namespace Chvdy\RedisModel;

interface RelationManagerInterface
{
    /**
     * Assign a relation between parent and related object
     *
     * @param string $relation
     * @param int $id_parent
     * @param int $id_related
     * @return bool
     * @throws \Chvdy\RedisModel\Exceptions\ParentObjectNotFound
     * @throws \Chvdy\RedisModel\Exceptions\RelatedObjectNotFound
     * @throws \Chvdy\RedisModel\Exceptions\AlreadyRelated
     * @throws \Chvdy\RedisModel\Exceptions\CannotSaveRelation
     */
    public function assign(string $relation, int $id_parent, int $id_related): bool;

    /**
     * Remove a relation between parent and related object
     *
     * @param string $relation
     * @param int $id_parent
     * @param int $id_related
     * @return int
     * @throws \Chvdy\RedisModel\Exceptions\HasNotRelated
     * @throws \Chvdy\RedisModel\Exceptions\CannotRemoveFromSet
     */
    public function unassign(string $relation, int $id_parent, int $id_related): int;

    /**
     * Remove all relations for a given parent ID
     *
     * @param int $id
     * @return int Number of relations removed
     */
    public function removeRelations(int $id): int;
}