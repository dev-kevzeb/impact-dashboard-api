<?php

namespace App\Modules\Donor\Service;

use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Repository\DonorRepository;
use RuntimeException;

class DonorService
{
    private DonorRepository $donorRepository;

    public function __construct(DonorRepository $donorRepository)
    {
        $this->donorRepository = $donorRepository;
    }

    public function createDonor(string $name): Donor
    {
        // Validar duplicados antes de crear
        if ($this->donorRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un donante con el nombre: {$name}");
        }
        
        $donor = Donor::at($name);
        
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
        
        // Validar duplicados (excepto el mismo registro)
        $existing = $this->donorRepository->exists('name', trim($name));
        if ($existing && strtolower(trim($donor->name)) !== strtolower(trim($name))) {
            throw new RuntimeException("Ya existe un donante con el nombre: {$name}");
        }
        
        $updatedDonor = Donor::at($name);
        $donor->name = $updatedDonor->name;
        
        $this->donorRepository->save($donor);
        
        return $donor;
    }
}
