<?php

namespace App\Modules\ProgramState\Service;

use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\ProgramState\Repository\ProgramStateRepository;
use RuntimeException;

class ProgramStateService
{
    private ProgramStateRepository $programStateRepository;

    public function __construct(ProgramStateRepository $programStateRepository)
    {
        $this->programStateRepository = $programStateRepository;
    }

    public function createProgramState(string $name): ProgramState
    {
        if ($this->programStateRepository->exists('name', trim($name))) {
            throw new RuntimeException("A status with the name already exists: {$name}");
        }

        $programState = ProgramState::at($name);
        $this->programStateRepository->save($programState);
        return $programState;
    }

    public function getProgramStateById(int $id): ProgramState
    {
        return $this->programStateRepository->findById($id);
    }

    public function findProgramStateByName(string $name): ProgramState
    {
        return $this->programStateRepository->findBy('name', $name);
    }

    public function getAllProgramStates(int $perPage = 10)
    {
        return $this->programStateRepository->paginate($perPage);
    }

    public function getProgramStatesPaginated(?string $search, int $perPage = 10)
    {
        return $this->programStateRepository->getPaginated($search, $perPage);
    }

    public function deleteProgramState(int $id): void
    {
        $this->programStateRepository->findById($id);

        if ($this->programStateRepository->hasRelations($id)) {
            throw new RuntimeException('The program status cannot be deleted because it is related to other records.');
        }

        $this->programStateRepository->delete($id);
    }

    public function updateProgramState(int $id, string $name): ProgramState
    {
        $programState = $this->programStateRepository->findById($id);
        try {
            $existing = $this->programStateRepository->findBy('name', trim($name));
            if ($existing && $existing->id !== $id) {
                throw new RuntimeException("Another status with the name already exists: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'not found')) {
                throw $e;
            }
        }
        $updated = ProgramState::at($name);
        $programState->name = $updated->name;
        $this->programStateRepository->save($programState);
        return $programState;
    }

    public function programStateExists(string $name): bool
    {
        return $this->programStateRepository->exists('name', $name);
    }
}
