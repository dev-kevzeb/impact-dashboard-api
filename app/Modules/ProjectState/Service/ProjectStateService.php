<?php 
namespace App\Modules\ProjectState\Service;
use App\Modules\ProjectState\Domain\ProjectState;
use App\Modules\ProjectState\Repository\ProjectStateRepository;

class ProjectStateService {
    private ProjectStateRepository $projectStateRepository;

    public function __construct(ProjectStateRepository $projectStateRepository)
    {
        $this->projectStateRepository = $projectStateRepository;
    }

    public function getAllProjectStates()
    {
        return $this->projectStateRepository->getAll();
    }
    public function createProjectState(string $state): ProjectState
    {

        if($this->projectStateRepository->exists('state', trim($state))){
            throw new \RuntimeException("El estado del proyecto ya existe: {$state}");
        }
        $projectState = ProjectState::at($state);
        $this->projectStateRepository->save($projectState);
        return $projectState;
    }

}