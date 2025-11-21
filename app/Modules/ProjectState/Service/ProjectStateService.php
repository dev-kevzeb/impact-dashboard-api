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
    public function getProjectStateById(int $id): ProjectState
    {   
        $projectState = $this->projectStateRepository->findById($id);
        if(!$projectState){
            throw new \RuntimeException("El estado del proyecto con id {$id} no existe.");
        }
        return $projectState;
    }
   public function updateProjectState(int $id, string $state): ProjectState
{
    $projectState = $this->getProjectStateById($id);
    
    // Validar duplicados (excepto el mismo registro)
    $existing = $this->projectStateRepository->exists('state', trim($state));
    if ($existing && strtolower(trim($projectState->state)) !== strtolower(trim($state))) {
        throw new \RuntimeException("El estado del proyecto ya existe: {$state}");
    }
    
    $updatedState = ProjectState::at($state);
    $projectState->state = $updatedState->state;
    $this->projectStateRepository->save($projectState);
    
    return $projectState;
}   

}