<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var T
     */
    protected Model $model;

    /**
     * @param T $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param T $entity
     */
    public function save(object $entity): void
    {
        try {
            $entity->save();
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al guardar entidad: " . $e->getMessage()
            );
        }
    }

    /**
     * @return T
     * @throws \RuntimeException Si la entidad no existe
     */
    public function findById(int $id): object
    {
        try {
            $entity = $this->model->find($id);
            
            if (!$entity) {
                throw new RuntimeException(
                    class_basename($this->model) . " no encontrado con ID: {$id}"
                );
            }
            
            return $entity;
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al buscar entidad por ID {$id}: " . $e->getMessage()
            );
        }
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return T
     * @throws \RuntimeException Si la entidad no existe
     */
    public function findBy(string $field, mixed $value): object
    {
        try {
            $entity = $this->model->where($field, $value)->first();
            
            if (!$entity) {
                throw new RuntimeException(
                    class_basename($this->model) . " no encontrado con {$field}={$value}"
                );
            }
            
            return $entity;
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al buscar entidad por {$field}={$value}: " . $e->getMessage()
            );
        }
    }

    /**
     * @return array<T>
     */
    public function getAll(): array
    {
        try {
            return $this->model->all()->toArray();
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al obtener todas las entidades: " . $e->getMessage()
            );
        }
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    public function exists(string $field, mixed $value): bool
    {
        try {
            return $this->model->where($field, $value)->exists();
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al verificar existencia de entidad: " . $e->getMessage()
            );
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        try {
            return $this->model->count();
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al contar entidades: " . $e->getMessage()
            );
        }
    }
}