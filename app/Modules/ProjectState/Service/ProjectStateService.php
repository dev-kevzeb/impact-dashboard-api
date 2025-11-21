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

    public function createProjectState(string $name): ProjectState
    {
        // NO duplicate check here - FormRequest handles it via Rule::unique()
        $projectState = ProjectState::at($name);  // Domain validation only
        $this->projectStateRepository->save($projectState);
        return $projectState;
    }

    public function getProjectStateById(int $id): ProjectState
    {   
        return $this->projectStateRepository->findById($id);
    }

    public function updateProjectState(int $id, string $name): ProjectState
    {
        $projectState = $this->getProjectStateById($id);
        
        // NO duplicate check here - FormRequest handles it via Rule::unique()->ignore($id)
        $updated = ProjectState::at($name);  // Re-validate domain rules
        $projectState->name = $updated->name;
        $this->projectStateRepository->save($projectState);
        
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