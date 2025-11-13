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
        $updatedDonor = Donor::at($name);
        $donor->name = $updatedDonor->name;
        
        $this->donorRepository->save($donor);
        
        return $donor;
    }
}
