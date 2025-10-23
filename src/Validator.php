<?php

namespace Chvdy\RedisModel;

class Validator implements ValidatorInterface
{
    public array $validationErrors = [];

    public function __construct(
        private readonly \CodeIgniter\Validation\Validation $validation,
        private readonly array $rules,
        private array $errors,
    ) {
        $this->parseErrors();
    }

    public function trimProtectedFields(\CodeIgniter\Entity\Entity $entity, array $protectFields = []): array
    {
        $data = $entity->toArray();
        foreach (array_merge($protectFields, $entity->dates) as $value) {
            unset($data[$value]);
        }

        return $data;
    }

    public function hasRules(string $key): bool
    {
        return isset($this->rules[$key]);
    }

    private function parseErrors(): void
    {
        foreach ($this->errors as $key => &$rules) {
            foreach ($rules as $rule => &$message) {
                $message = lang($message);
            }
        }
    }

    public function check(string $key, $value): bool
    {
        $result = $this->validation
            ->reset()
            ->check($value, $this->rules[$key], $this->errors[$key]);

        if (!$result) {
            $this->validationErrors = array_merge($this->validationErrors, $this->validation->getErrors());

            return false;
        }

        return true;
    }

    public function validate(\CodeIgniter\Entity\Entity $entity): bool
    {
        $result =$this->validation
            ->reset()
            ->setRules($this->rules, $this->errors)
            ->run($entity->toArray());
        $this->validationErrors = $this->validation->getErrors();

        return $result;
    }
}