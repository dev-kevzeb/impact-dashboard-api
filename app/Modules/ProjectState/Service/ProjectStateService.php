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
            throw new \RuntimeException("The project status already exists: {$state}");
        }

        $projectState = ProjectState::at($state);
        $this->projectStateRepository->save($projectState);
        return $projectState;
    }

    public function getProjectStateById(int $id): ProjectState
    {   
        $projectState = $this->projectStateRepository->findById($id);
        if(!$projectState) throw new \RuntimeException("The project status with id {$id} does not exist.");
        return $this->projectStateRepository->findById($id);
    }

    public function updateProjectState(int $id, string $state): ProjectState
    {
        $projectState = $this->getProjectStateById($id);
        if(!$projectState){
            throw new \RuntimeException("The project status with id {$id} does not exist.");
        }
        $existing = $this->projectStateRepository->findByState(trim($state));
        if($existing && $existing->id !== $id){
            throw new \RuntimeException("The project status already exists: {$state}");
        }
        $projectState->update(['state' => $state]);
        $this->projectStateRepository->save($projectState);
        return $projectState;
    }   

    public function findProjectStateByName(string $state): ProjectState
    {
        return $this->projectStateRepository->findBy('state', $state);
    }

    public function getProjectStatesPaginated(?string $search, int $perPage){
        return $this->projectStateRepository->getPaginated($search, $perPage);
    }

    public function deleteProjectState(int $id): void
    {
        $this->projectStateRepository->findById($id);

        if ($this->projectStateRepository->hasRelations($id)) {
            throw new \RuntimeException('The project status cannot be deleted because it is related to other records.');
        }

        $this->projectStateRepository->delete($id);
    }

}