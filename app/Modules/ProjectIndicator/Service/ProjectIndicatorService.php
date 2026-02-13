<?php

namespace App\Modules\ProjectIndicator\Service;

use App\Modules\Indicator\Repository\IndicatorRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use App\Modules\ProjectIndicator\Repository\ProjectIndicatorRepository;
use RuntimeException;

class ProjectIndicatorService
{
    private ProjectIndicatorRepository $projectIndicatorRepository;
    private ProjectRepository $projectRepository;
    private IndicatorRepository $indicatorRepository;

    public function __construct(ProjectIndicatorRepository $projectIndicatorRepository, ProjectRepository $projectRepository, IndicatorRepository $indicatorRepository)
    {
        $this->projectIndicatorRepository = $projectIndicatorRepository;
        $this->projectRepository = $projectRepository;
        $this->indicatorRepository = $indicatorRepository;
    }

    public function getAllProjectIndicators()
    {
        return $this->projectIndicatorRepository->getAll();
    }

    public function findProjectsByIndicatorId(int $indicatorId)
    {
        $indicator = $this->indicatorRepository->findById($indicatorId);
        if(empty($indicator)) throw new RuntimeException("El Indicador con id {$indicatorId} no existe.");
        return $indicator->projects;
    }

    public function findIndicatorsByProjectId(int $projectId)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new RuntimeException("El proyecto con id {$projectId} no existe.");
        return $project->indicators;
    }
 
    public function findIndicatorsByProjectName(string $projectName)
    {
        $project = $this->projectRepository->findBy('name',$projectName);
        if (empty($project)) throw new RuntimeException("El proyecto con nombre {$projectName} no existe.");
        return $project->indicators;
    }

    public function createProjectIndicator(int $projectId, int $indicatorId)
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new RuntimeException("El proyecto con id {$projectId} no existe.");
        $indicator = $this->indicatorRepository->findById($indicatorId);
        if(empty($indicator)) throw new RuntimeException("El Indicador con id {$indicatorId} no existe.");

        $projectAgency = $this->projectIndicatorRepository->findByProjectAndIndicator($indicatorId,$projectId);
        if(!empty($projectAgency)) throw new RuntimeException("La relación entre el proyecto con id {$projectId} y el indicador con id {$indicatorId} ya existe.");

        
        $projectIndicator = ProjectIndicator::at($project, $indicator);

        $this->projectIndicatorRepository->save($projectIndicator);
        return $projectIndicator;
    }

    public function deleteProjectIndicator(int $projectIndicatorId)
    {
        $projectIndicator = $this->projectIndicatorRepository->findById($projectIndicatorId);
        if (empty($projectIndicator)) throw new RuntimeException("La relación entre el proyecto y la agencia con id {$projectIndicatorId} no existe.");
        $this->projectIndicatorRepository->delete($projectIndicator->id);
    }

    public function deleteAllByProjectId(int $projectId): void
    {
        $project = $this->projectRepository->findById($projectId);
        if (empty($project)) throw new RuntimeException("The project with id {$projectId} does not exist.");
        

        $this->projectIndicatorRepository->deleteByProjectId($projectId);
    }

    public function getProjectIdsByIndicatorIds(array $indicatorIds){
        return $this->projectIndicatorRepository->getProjectIdsByIndicatorIds($indicatorIds);
    }

}