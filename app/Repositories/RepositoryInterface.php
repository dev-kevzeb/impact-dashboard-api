<?php

namespace App\Repositories;

/**
 * @template T of object
 */
interface RepositoryInterface
{
    /**
     * @param T $entity
     */
    public function save(object $entity): void;

    /**
     * @return T|null
     */
    public function findById(int $id): ?object;

    /**
     * @return T|null
     */
    public function findBy(string $field, mixed $value): ?object;

    /**
     * @return array<T>
     */
    public function getAll(): array;

    public function exists(string $field, mixed $value): bool;

    public function count(): int;
}