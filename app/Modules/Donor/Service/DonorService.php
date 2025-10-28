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

        $updatedDonor = Donor::at($name);
        $donor->name = $updatedDonor->name;
        
        $this->donorRepository->save($donor);
        
        return $donor;
    }

    public function donorExists(string $name): bool
    {
        return $this->donorRepository->exists('name', $name);
    }
}
