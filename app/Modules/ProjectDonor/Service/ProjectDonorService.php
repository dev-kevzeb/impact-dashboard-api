<?php

namespace App\Modules\ProjectDonor\Service;

use App\Modules\Donor\Repository\DonorRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectDonor\Domain\ProjectDonor;
use App\Modules\ProjectDonor\Repository\ProjectDonorRepository;
use RuntimeException;

class ProjectDonorService
{
    private ProjectDonorRepository $projectDonorRepository;
    private DonorRepository $donorRepository;
    private ProjectRepository $projectRepository;

    public function __construct(ProjectDonorRepository $projectDonorRepository, DonorRepository $donorRepository, ProjectRepository $projectRepository)
    {
        $this->donorRepository = $donorRepository;
        $this->projectDonorRepository = $projectDonorRepository;
        $this->projectRepository = $projectRepository;
    }


    public function createProjectDonor(int $projectId, int $donorId, float $contribution)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new RuntimeException("The project with id {$projectId} does not exists.");
        
        $donor = $this->donorRepository->findById($donorId);
        if (empty($donor)) throw new RuntimeException("The donor with id {$donorId} does not exists.");

        $projectDonor = $this->projectDonorRepository->findByProjectAndDonor($donorId,$projectId);
        if(!empty($projectAgency)) throw new RuntimeException("The relationship between the project with id {$projectId} and the donor with id {$donorId} already exists.");

        $projectDonor = ProjectDonor::at($project, $donor, $contribution);

        $this->projectDonorRepository->save($projectDonor);
        return $projectDonor;
    }

    public function deleteAllByProjectId(int $projectId)
    {
        $project = $this->projectRepository->findById($projectId);
        if(empty($project)) throw new RuntimeException("The project with id {$projectId} does not exist.");

        $this->projectDonorRepository->deleteByProjectId($projectId);
    }

}