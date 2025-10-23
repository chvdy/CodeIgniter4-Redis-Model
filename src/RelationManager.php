<?php

namespace Chvdy\RedisModel;

class RelationManager implements RelationManagerInterface
{
    public function __construct(
        private readonly \Chvdy\RedisModel\Client $client,
        private readonly \Chvdy\RedisModel\KeyManager $keyManager,
        private readonly array $has_many
    ) {
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\ParentObjectNotFound
     * @throws \Chvdy\RedisModel\Exceptions\CannotGetSetMembers
     * @throws \Chvdy\RedisModel\Exceptions\NotFound
     */
    public function getRelated(string $relation, int $id_parent): array
    {
        $model = model($this->has_many[$relation]);
        $parent = $this->client->connection()
            ->hExists($this->keyManager->getKey($id_parent), 'id');
        if ($parent === false) {
            throw new Exceptions\ParentObjectNotFound(
                lang('Exceptions.ParentObjectNotFound', [$id_parent, $relation]),
                Enums\ExceptionCodes::$GetRelatedParentObjectNotFound);
        }

        $members = $this->client->connection()
            ->sMembers($this->keyManager->getRelationKey($relation, $id_parent));
        if ($members === false) {
            throw new Exceptions\CannotGetSetMembers(
                lang('Exceptions.CannotGetSetMembers'),
                Enums\ExceptionCodes::$CannotGetSetMembers);
        }

        $collection = [];
        foreach ($members as $member) {
            $collection[] = $model->get($member);
        }

        return $collection;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\ParentObjectNotFound
     * @throws \Chvdy\RedisModel\Exceptions\RelatedObjectNotFound
     * @throws \Chvdy\RedisModel\Exceptions\AlreadyRelated
     * @throws \Chvdy\RedisModel\Exceptions\CannotSaveRelation
     */
    public function assign(string $relation, int $id_parent, int $id_related): bool
    {
        $model = model($this->has_many[$relation]);

        $parent = $this->client->connection()
            ->hExists($this->keyManager->getKey($id_parent), 'id');
        if (!$parent) {
            throw new Exceptions\ParentObjectNotFound(
                lang('Exceptions.ParentObjectNotFound', [$id_parent, $relation]), Enums\ExceptionCodes::$ParentObjectNotFound);
        }

        $related = $this->client->connection()
            ->hExists($model->repository->keyManager->getKey($id_related), 'id');
        if (!$related) {
            throw new Exceptions\RelatedObjectNotFound(
                lang('Exceptions.RelatedObjectNotFound', [$id_parent, $relation, $id_related]), Enums\ExceptionCodes::$RelatedObjectNotFound);
        }

        if ($this->client->connection()->sIsMember($this->keyManager->getRelationKey($relation, $id_parent), $id_related)) {
            throw new Exceptions\AlreadyRelated(
                lang('Exceptions.AlreadyRelated', [$id_parent, $relation]), Enums\ExceptionCodes::$AlreadyRelated);
        }

        $result = $this->client->connection()
            ->sAdd($this->keyManager->getRelationKey($relation, $id_parent), $id_related);

        if ($result === false) {
            throw new Exceptions\CannotSaveRelation(
                lang('Exceptions.CannotSaveRelation', [$id_parent]), Enums\ExceptionCodes::$CannotSaveRelation);
        }

        return (bool)$result;
    }

    /**
     *  @throws \Chvdy\RedisModel\Exceptions\HasNotRelated
     *  @throws \Chvdy\RedisModel\Exceptions\CannotRemoveFromSet
     */
    public function unassign(string $relation, int $id_parent, int $id_related): int
    {
        if (!$this->client->connection()
            ->sIsMember($this->keyManager->getRelationKey($relation, $id_parent), $id_related)) {

            throw new Exceptions\HasNotRelated(
                lang('Exceptions.HasNotRelated', [$id_parent, $relation, $id_related]), Enums\ExceptionCodes::$HasNotRelated);
        }

        $result = $this->client->connection()
            ->sRem($this->keyManager->getRelationKey($relation, $id_parent), $id_related);

        if ($result === false) {
            throw new Exceptions\CannotRemoveFromSet(
                lang('Exceptions.CannotRemoveFromSet', [$id_parent, $relation]), Enums\ExceptionCodes::$CannotRemoveFromSet);
        }

        return $result;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     */
    public function removeRelations(int $id): int
    {
        $deleted = 0;
        foreach ($this->has_many as $relation => $class) {
            $result = $this->client->connection()->del($this->keyManager->getRelationKey($relation, $id));

            if ($result > 0) {
                $deleted += $result;
            }
        }

        return $deleted;
    }
}