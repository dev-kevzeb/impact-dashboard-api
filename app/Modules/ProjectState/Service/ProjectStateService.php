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
        // Validar duplicados
        if ($this->projectStateRepository->exists('state', trim($state))) {
            throw new \RuntimeException("Ya existe un estado del proyecto con el nombre: {$state}");
        }

        $projectState = ProjectState::at($state);
        $this->projectStateRepository->save($projectState);
        return $projectState;
    }

    public function getProjectStateById(int $id): ProjectState
    {   
        return $this->projectStateRepository->findById($id);
    }

    public function updateProjectState(int $id, string $state): ProjectState
    {
        $projectState = $this->getProjectStateById($id);
        
        // Validar duplicados (excepto el mismo registro)
        $existing = $this->projectStateRepository->exists('state', trim($state));
        if ($existing && strtolower(trim($projectState->state)) !== strtolower(trim($state))) {
            throw new \RuntimeException("Ya existe un estado del proyecto con el nombre: {$state}");
        }
        
        $updated = ProjectState::at($state);
        $projectState->state = $updated->state;
        $this->projectStateRepository->save($projectState);
        
        return $projectState;
    }

    public function findProjectStateByName(string $state): ProjectState
    {
        return $this->projectStateRepository->findBy('state', $state);
    }

}