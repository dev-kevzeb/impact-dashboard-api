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

    public function createProjectAgency(int $projectId, int $agencyId)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new \RuntimeException("El proyecto con id {$projectId} no existe.");
        $agency = $this->agencyRepository->findById($agencyId);
        if (empty($agency)) throw new \RuntimeException("La agencia con id {$agencyId} no existe.");

        $projectAgency = $this->projectAgencyRepository->findByProjectAndAgency($agencyId, $projectId);
        if (!empty($projectAgency)) throw new \RuntimeException("La relación entre el proyecto con id {$projectId} y la agencia con id {$agencyId} ya existe.");

        $projectAgency = ProjectAgency::at($project, $agency);
        $this->projectAgencyRepository->save($projectAgency);
        return $projectAgency;
    }

    public function deleteProjectAgency(int $projectAgencyId)
    {
        $projectAgency = $this->projectAgencyRepository->findById($projectAgencyId);
        if (empty($projectAgency)) throw new \RuntimeException("La relación entre el proyecto y la agencia con id {$projectAgencyId} no existe.");
        $this->projectAgencyRepository->delete($projectAgency->id);
    }

}