<?php

namespace App\Modules\Program\Service;

use App\Modules\Program\Domain\Program;
use App\Modules\Program\Repository\ProgramRepository;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\ProgramState\Repository\ProgramStateRepository;
use App\Modules\Sdg\Repository\SdgRepository;
use RuntimeException;

class ProgramService
{
    private ProgramRepository $programRepository;
    private ContactRepository $contactRepository;
    private ProgramStateRepository $programStateRepository;
    private SdgRepository $sdgRepository;

    public function __construct(
        ProgramRepository $programRepository,
        ContactRepository $contactRepository,
        ProgramStateRepository $programStateRepository,
        SdgRepository $sdgRepository
    ) {
        $this->programRepository = $programRepository;
        $this->contactRepository = $contactRepository;
        $this->programStateRepository = $programStateRepository;
        $this->sdgRepository = $sdgRepository;
    }

    /**
     * Crear un nuevo programa
     */
    public function createProgram(
        string $name,
        string $description,
        ?string $bannerImg,
        string $programUrl,
        int $contactId,
        array $sdgIds = []
    ): Program {
        // Validar duplicados
        if ($this->programRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un programa con el nombre: {$name}");
        }

        // Forzar estado "Inactivo" al crear (regla de negocio)
        // Solo cambiará a "Activo" cuando tenga proyectos asociados
        $inactiveState = $this->programStateRepository->findBy('name', 'Inactivo');

        // Validar Contact
        $contact = $this->contactRepository->findById($contactId);

        // Validar SDGs si existen
        if (!empty($sdgIds)) {
            foreach ($sdgIds as $sdgId) {
                $this->sdgRepository->findById($sdgId);
            }
        }

        // Usar el estado Inactivo encontrado
        $programState = $inactiveState;

        // Crear programa usando factory method con objetos
        $program = Program::at(
            $name,
            $description,
            $bannerImg,
            $programUrl,
            $contact,
            $programState
        );

        // Guardar en base de datos
        $this->programRepository->save($program);

        // Sincronizar relaciones M:N
        if (!empty($sdgIds)) {
            $this->programRepository->syncSdgs($program, $sdgIds);
        }

        return $program->fresh(['contact', 'programState', 'sdgs']);
    }

    /**
     * Obtener programa por ID
     */
    public function getProgramById(int $id): Program
    {
        return $this->programRepository->findByIdWithRelations($id);
    }

    /**
     * Obtener todos los programas
     */
    public function getAllPrograms()
    {
        return $this->programRepository->getAllWithRelations();
    }

    /**
     * Actualizar un programa
     */
    public function updateProgram(
        int $id,
        string $name,
        string $description,
        ?string $bannerImg,
        string $programUrl,
        int $contactId,
        int $programStateId,
        array $sdgIds = []
    ): Program {
        // Obtener programa existente
        $program = $this->programRepository->findById($id);

        // Validar duplicados (excepto el actual)
        $existing = $this->programRepository->exists('name', trim($name));
        if ($existing && strtolower(trim($program->name)) !== strtolower(trim($name))) {
            throw new RuntimeException("Ya existe un programa con el nombre: {$name}");
        }

        // Validar entidades relacionadas
        $this->validateRelatedEntities(
            $contactId,
            $programStateId,
            $sdgIds
        );

        // Obtener objetos de las entidades relacionadas
        $contact = $this->contactRepository->findById($contactId);
        $programState = $this->programStateRepository->findById($programStateId);

        // Validar datos con factory method (sin guardar)
        Program::at(
            $name,
            $description,
            $bannerImg,
            $programUrl,
            $contact,
            $programState
        );

        // Actualizar campos
        $program->name = trim($name);
        $program->description = trim($description);
        $program->banner_img = $bannerImg ? trim($bannerImg) : $program->banner_img;
        $program->program_url = trim($programUrl);
        $program->contact_id = $contactId;
        $program->program_state_id = $programStateId;
        
        // Guardar cambios
        $this->programRepository->save($program);

        // Sincronizar relaciones M:N
        $this->programRepository->syncSdgs($program, $sdgIds);

        return $program->fresh(['contact', 'programState', 'sdgs']);
    }

    /**
     * Buscar programa por nombre
     */
    public function findProgramByName(string $name): Program
    {
        return $this->programRepository->findBy('name', $name);
    }

    /**
     * Validar que existan todas las entidades relacionadas
     */
    private function validateRelatedEntities(
        int $contactId,
        int $programStateId,
        array $sdgIds
    ): void {
        // Validar Contact
        $this->contactRepository->findById($contactId);

        // Validar ProgramState
        $this->programStateRepository->findById($programStateId);

        // Validar SDGs
        foreach ($sdgIds as $sdgId) {
            $this->sdgRepository->findById($sdgId);
        }
    }
}
