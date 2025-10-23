<?php

namespace Chvdy\RedisModel;

abstract class Model
{
    public \Chvdy\RedisModel\Repository $repository;
    public \Chvdy\RedisModel\Validator $validator;
    public \Chvdy\RedisModel\RelationManager $relationManager;
    protected array $protectFields = [];


    protected array $rules = [];
    protected array $errors = [];
    protected string $entity = '';
    /**
     * @var array $has_many = [
     *      relation_name => \App\Models\Model::class,
     * ]
     */
    protected array $has_many = [];

    public function __construct() {
        $validation = service('validation');
        $client = \CodeIgniter\Config\Factories::libraries(\Chvdy\RedisModel\Client::class);
        $keyManager = new \Chvdy\RedisModel\KeyManager($client, $this->entity, $this->has_many);
        $this->validator = new \Chvdy\RedisModel\Validator(
            $validation,
            $this->rules,
            $this->errors
        );
        $this->relationManager = new \Chvdy\RedisModel\RelationManager($client, $keyManager, $this->has_many);
        $this->repository = new \Chvdy\RedisModel\Repository(
            $client,
            $keyManager,
            $this->relationManager
        );
    }

    protected function createEntity(array $data, ?int $id = null): \CodeIgniter\Entity\Entity
    {
        $entity = new $this->entity();
        $entity->fill($data);

        if ($id !== null) {
            $entity->id = $id;
        }

        if (!empty($entity->dates) && in_array('created_at', $entity->dates) && empty($entity->created_at)) {
            $entity->created_at = 'now';
        }

        return $entity;
    }

    public function setProtectFields(array $fields, bool $flush = false): void
    {
        if ($flush) {
            $this->protectFields = $fields;

            return;
        }

        $this->protectFields = array_merge($this->protectFields, $fields);
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\NotFound
     */
    public function get(int $id): \CodeIgniter\Entity\Entity
    {
        $data = $this->repository->get($id);
        $entity = $this->createEntity($data, $id);

        return $entity;
    }

    /**
     * @return \CodeIgniter\Entity\Entity[]
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     * @throws \Chvdy\RedisModel\Exceptions\CannotGetIds
     */
    public function getAll(): array
    {
        $collection = [];
        foreach ($this->repository->getAll() as $record) {
            $collection[] = $this->createEntity($record);
        }

        return $collection;
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\Validation
     * @throws \Chvdy\RedisModel\Exceptions\WrongIncrementId
     * @throws \Chvdy\RedisModel\Exceptions\CannotSaveHashCollectionFields
     * @throws \Chvdy\RedisModel\Exceptions\ConnectionFailed
     */
    public function create(array $data): int
    {
        $entity = $this->createEntity($data);

        if (!$this->validator->validate($entity)) {
            throw new Exceptions\Validation(
                lang('Exceptions.Validation'),
                Enums\ExceptionCodes::$Validation,
                $this->validator->validationErrors);
        }

        $data = [];
        foreach ($entity->toArray() as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (in_array($key, $entity->dates ?? []) && is_object($value)) {
                $data[$key] = $entity->$key->toDateTimeString();
                continue;
            }
            $data[$key] = $value;
        }

        return $this->repository->insert($data);
    }

    /**
     * @throws \Chvdy\RedisModel\Exceptions\NotFound
     * @throws \Chvdy\RedisModel\Exceptions\NoChangesDetected
     * @throws \Chvdy\RedisModel\Exceptions\Validation
     */
    public function update(int $id, array $data, array $protectedFields = []): bool
    {
        $entity = $this->createEntity($data, $id);
        $this->setProtectFields($protectedFields, true);

        $current = $this->validator->trimProtectedFields($this->get($entity->id), $this->protectFields);
        $candidate = $this->validator->trimProtectedFields($entity, $this->protectFields);

        $diff = array_diff($candidate, $current);
        if (empty($diff)) {
            throw new Exceptions\NoChangesDetected(
                lang('Exceptions.NoChangesDetected', [$entity->id]), Enums\ExceptionCodes::$NoChangesDetected);
        }

        $data = [];
        foreach ($diff as $key => $value) {
            if (is_null($value) ||
                !$this->validator->hasRules($key) ||
                !$this->validator->check($key, $value)) {

                continue;
            }

            $data[$key] = $value;
        }

        if (!empty($this->validator->validationErrors)) {
            throw new Exceptions\Validation(
                lang('Exceptions.Validation'), Enums\ExceptionCodes::$Validation, $this->validator->validationErrors);
        }

        $entity->updated_at = 'now';
        $data['updated_at'] = $entity->updated_at->toDateTimeString();

        return $this->repository->update($id, $data);
    }

}
