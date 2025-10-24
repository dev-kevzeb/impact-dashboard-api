<?php

namespace App\Modules\Donor\Service;

use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Repository\DonorRepository;
use RuntimeException;

class DonorService
{
    public function __construct(
        private readonly DonorRepository $donorRepository
    ) {}

    public function createDonor(string $name): Donor
    {
        // Validar que no exista un donante con el mismo nombre
        if ($this->donorRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un donante con el nombre: {$name}");
        }

        // Crear usando Factory Method del Domain (validaciones internas)
        $donor = Donor::at($name);
        
        // Persistir usando Repository genérico
        $this->donorRepository->save($donor);
        
        return $donor;
    }

    public function getDonorById(int $id): Donor
    {
        return $this->donorRepository->findById($id);
    }

    public function findDonorByName(string $name): Donor
    {
        return $this->donorRepository->findBy('name', $name);
    }

    public function getAllDonors()
    {
        return $this->donorRepository->getAll();
    }

    public function updateDonor(int $id, string $name): Donor
    {
        $donor = $this->donorRepository->findById($id);

        // Validar que el nuevo nombre no esté duplicado (excepto el actual)
        try {
            $existingDonor = $this->donorRepository->findBy('name', trim($name));
            if ($existingDonor && $existingDonor->id !== $id) {
                throw new RuntimeException("Ya existe otro donante con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e;
            }
        }

        // Validar el nuevo nombre usando las reglas del dominio
        $updatedDonor = Donor::at($name);
        $donor->name = $updatedDonor->name;
        
        $this->donorRepository->save($donor);
        
        return $donor;
    }

    public function donorExists(string $name): bool
    {
        return $this->donorRepository->exists('name', $name);
    }

    public function getTotalDonors(): int
    {
        return $this->donorRepository->count();
    }

    public function getStats(): array
    {
        return [
            'total_donors' => $this->donorRepository->count(),
            'active_donors' => $this->donorRepository->count(), // Todos están activos por defecto
            'created_today' => $this->getDonorsCreatedToday(),
            'created_this_month' => $this->getDonorsCreatedThisMonth()
        ];
    }

    private function getDonorsCreatedToday(): int
    {
        return $this->donorRepository->countByDateRange(
            now()->startOfDay(),
            now()->endOfDay()
        );
    }

    private function getDonorsCreatedThisMonth(): int
    {
        return $this->donorRepository->countByDateRange(
            now()->startOfMonth(),
            now()->endOfMonth()
        );
    }
}
