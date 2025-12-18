<?php

namespace App\Modules\Donor\Service;

use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Repository\DonorRepository;
use App\Modules\Project\Repository\ProjectRepository;
use RuntimeException;

class DonorService
{
    private DonorRepository $donorRepository;
    private ProjectRepository $projectRepository;

    public function __construct(DonorRepository $donorRepository, ProjectRepository $projectRepository)
    {
        $this->donorRepository = $donorRepository;
        $this->projectRepository = $projectRepository;
    }

    public function createDonor(string $name, $contribution, $project_id): Donor
    {
        $project = $this->projectRepository->findById($project_id);
        if(empty($project)) throw new RuntimeException("Project with id not found: {$project_id}.");
        
        $donor = Donor::at($name, $contribution, $project);
        
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

    public function updateDonor(int $id, string $name, $contribution, $project_id): Donor
    {
        $donor = $this->donorRepository->findById($id);
        if(empty($donor)) throw new RuntimeException("The donor with id was not found: {$id}.");

        $project = $this->projectRepository->findById($project_id);
        if(empty($project)) throw new RuntimeException("Project with id not found: {$project_id}.");

        $updatedDonor = Donor::at($name, $contribution, $project);
        $donor->name = $updatedDonor->name;
        $donor->contribution = $updatedDonor->contribution;
        $donor->project = $updatedDonor->project;
        
        $this->donorRepository->save($donor);
        
        return $donor;
    }
}
