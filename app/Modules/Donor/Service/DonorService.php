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
        $donor = $this->donorRepository->findByName($name);
        if(!empty($donor)) throw new RuntimeException("A donor with the name '{$name}' already exists.");
        
        $donor = Donor::at($name);     
        $this->donorRepository->save($donor);
        return $donor;
    }

    public function updateDonor(int $id, string $name): Donor
    {        
        $donor = $this->donorRepository->findByName($name);
        if(!empty($donor) && $donor->id !== $id) throw new RuntimeException("A donor with the name '{$name}' already exists.");

        $donor = $this->donorRepository->findById($id);
        if(empty($donor)) throw new RuntimeException("The donor with id was not found: {$id}.");

        $updatedDonor = Donor::at($name);
        $donor->name = $updatedDonor->name;
        
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

    public function getDonorsExcluding(int $perPage, ?string $search, ?array $exclude){
        return $this->donorRepository->getExcluding($search, $perPage, $exclude);
    }

    public function deleteDonor(int $id): void
    {
        $this->donorRepository->findById($id);

        if ($this->donorRepository->hasRelations($id)) {
            throw new RuntimeException('The donor cannot be deleted because it is related to other records.');
        }

        $this->donorRepository->delete($id);
    }
}
