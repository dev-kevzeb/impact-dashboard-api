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
            throw new RuntimeException("Ya existe un estado con el nombre: {$name}");
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

    public function getAllProgramStates()
    {
        return $this->programStateRepository->getAll();
    }

    public function updateProgramState(int $id, string $name): ProgramState
    {
        $programState = $this->programStateRepository->findById($id);
        try {
            $existing = $this->programStateRepository->findBy('name', trim($name));
            if ($existing && $existing->id !== $id) {
                throw new RuntimeException("Ya existe otro estado con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
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
