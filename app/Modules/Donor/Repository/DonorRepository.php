<?php

namespace App\Repositories;

use App\Models\Donor;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DonorRepository implements DonorRepositoryContract
{
    private const TABLE_NAME = 'donors';

    public function save(Donor $donor): void
    {
        try {
            DB::table(self::TABLE_NAME)->insertOrIgnore([
                'name' => trim($donor->getName()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al guardar donor '{$donor->getName()}': " . $e->getMessage()
            );
        }
    }

    public function findByName(string $name): ?Donor
    {
        try {
            $row = DB::table(self::TABLE_NAME)
                ->where('name', trim($name))
                ->first();

            if (!$row) {
                return null;
            }

            return Donor::at($row->name);
            
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al buscar donor '{$name}': " . $e->getMessage()
            );
        }
    }

    public function getAll(): array
    {
        try {
            return DB::table(self::TABLE_NAME)
                ->orderBy('name')
                ->get()
                ->map(fn($row) => Donor::at($row->name))
                ->toArray();
                
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al obtener todos los donors: " . $e->getMessage()
            );
        }
    }

    public function delete(Donor $donor): bool
    {
        try {
            $deletedRows = DB::table(self::TABLE_NAME)
                ->where('name', trim($donor->getName()))
                ->delete();

            return $deletedRows > 0;
            
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al eliminar donor '{$donor->getName()}': " . $e->getMessage()
            );
        }
    }

    public function exists(string $name): bool
    {
        try {
            return DB::table(self::TABLE_NAME)
                ->where('name', trim($name))
                ->exists();
                
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al verificar existencia de donor '{$name}': " . $e->getMessage()
            );
        }
    }

    public function count(): int
    {
        try {
            return DB::table(self::TABLE_NAME)->count();
            
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al contar donors: " . $e->getMessage()
            );
        }
    }
}