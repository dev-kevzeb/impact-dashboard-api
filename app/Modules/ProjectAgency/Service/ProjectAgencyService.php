<?php

namespace App\Modules\ProjectAgency\Service;

use App\Modules\Agency\Repository\AgencyRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectAgency\Domain\ProjectAgency;
use App\Modules\ProjectAgency\Repository\ProjectAgencyRepository;

class ProjectAgencyService
{
    private ProjectAgencyRepository $projectAgencyRepository;
    private ProjectRepository $projectRepository;
    private AgencyRepository $agencyRepository;

    public function __construct(ProjectAgencyRepository $projectAgencyRepository, ProjectRepository $projectRepository, AgencyRepository $agencyRepository)
    {
        $this->projectAgencyRepository = $projectAgencyRepository;
        $this->projectRepository = $projectRepository;
        $this->agencyRepository = $agencyRepository;
    }

    public function getAllProjectAgencies()
    {
        return $this->projectAgencyRepository->getAll();
    }

    public function findProjectsByAgencyId(int $agencyId)
    {
        $agency = $this->agencyRepository->findById($agencyId);
        if (empty($agency)) throw new \RuntimeException("La agencia con id {$agencyId} no existe.");
        return $agency->projects;
    }

    public function findProjectsByAgencyName(string $name)
    {
        $agency = $this->agencyRepository->findBy('name', $name);
        if (empty($agency)) throw new \RuntimeException("La agencia con nombre {$name} no existe.");
        return $agency->projects;
    }

    public function findAgenciesByProjectId(int $projectId)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new \RuntimeException("El proyecto con id {$projectId} no existe.");
        return $project->agencies;
    }

    public function findAgenciesByProjectName(string $name)
    {
        $project = $this->projectRepository->findBy('name', $name);
        if (empty($project)) throw new \RuntimeException("El proyecto con nombre {$name} no existe.");
        return $project->agencies;
    }

    public function createProjectAgency(int $projectId, int $agencyId, float $contribution)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new \RuntimeException("The project with id {$projectId} does not exist.");
        $agency = $this->agencyRepository->findById($agencyId);
        if (empty($agency)) throw new \RuntimeException("The agency with id {$agencyId} does not exist.");

        $projectAgency = $this->projectAgencyRepository->findByProjectAndAgency($agencyId, $projectId);
        if (!empty($projectAgency)) throw new \RuntimeException("The relationship between the project with id {$projectId} and the agency with id {$agencyId} already exist.");

        $projectAgency = ProjectAgency::at($project, $agency, $contribution);
        $this->projectAgencyRepository->save($projectAgency);
        return $projectAgency;
    }

    public function deleteProjectAgency(int $projectAgencyId)
    {
        $projectAgency = $this->projectAgencyRepository->findById($projectAgencyId);
        if (empty($projectAgency)) throw new \RuntimeException("La relación entre el proyecto y la agencia con id {$projectAgencyId} no existe.");
        $this->projectAgencyRepository->delete($projectAgency->id);
    }

    public function deleteAllByProjectId(int $projectId): void
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new \RuntimeException("The project with id {$projectId} does not exist.");
        

        $this->projectAgencyRepository->deleteByProjectId($projectId);
    }



}