<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

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
     * @return T
     * @throws \RuntimeException Si la entidad no existe
     */
    public function findById(int $id): object;

    /**
     * @return T
     * @throws \RuntimeException Si la entidad no existe
     */
    public function findBy(string $field, mixed $value): object;

    /**
     * @return Collection<int, T>
     */
    public function getAll();    
    
    public function count(): int;

    public function countByDateRange(\DateTime $startDate, \DateTime $endDate): int;

    /**
     * @param string $field Nombre del campo a buscar
     * @param mixed $value Valor a buscar
     * @return bool
     */
    public function exists(string $field, mixed $value): bool;
}