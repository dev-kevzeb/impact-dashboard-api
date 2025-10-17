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

    public function getAllDonors(): array
    {
        return $this->donorRepository->getAll();
    }

    public function updateDonor(int $id, string $name): Donor
    {
        $donor = $this->donorRepository->findById($id);

        // Validar que el nuevo nombre no esté duplicado (excepto el actual)
        $existingDonor = $this->donorRepository->findBy('name', trim($name));
        if ($existingDonor && $existingDonor->id !== $id) {
            throw new RuntimeException("Ya existe otro donante con el nombre: {$name}");
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
}
