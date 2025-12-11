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
        array $contactPayload,
        array $sdgIds = []
    ): Program {
        // Validar duplicados
        if ($this->programRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un programa con el nombre: {$name}");
        }

        // Manejar Contact (nuevo o existente)
        if (!empty($contactPayload['id'])) {
            // Caso 1: Contact existente
            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) {
                throw new RuntimeException("El contacto con id {$contactPayload['id']} no existe.");
            }
        } else {
            // Caso 2: Crear nuevo Contact
            $contact = \App\Modules\Contact\Domain\Contact::at(
                $contactPayload['first_name'],
                $contactPayload['last_name'],
                $contactPayload['title'],
                $contactPayload['email'],
                $contactPayload['phone'] ?? ''
            );
            $this->contactRepository->save($contact);
        }

        // Forzar estado "Inactivo" al crear (regla de negocio)
        // Solo cambiará a "Activo" cuando tenga proyectos asociados
        $inactiveState = $this->programStateRepository->findBy('name', 'Inactivo');

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
     * Obtener todos los programas con paginación
     * @param int $perPage Número de registros por página (default: 10)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPrograms(int $perPage = 10)
    {
        return $this->programRepository->paginateWithRelations($perPage);
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
        array $contactPayload,
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

        // Manejar Contact (nuevo, existente o actualizar)
        if (!empty($contactPayload['id'])) {
            // Caso 1: Contact existente - buscar
            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) {
                throw new RuntimeException("El contacto con id {$contactPayload['id']} no existe.");
            }
            
            // Si vienen datos adicionales, ACTUALIZAR el contact
            if (isset($contactPayload['first_name']) && isset($contactPayload['last_name']) 
                && isset($contactPayload['title']) && isset($contactPayload['email'])) {
                
                $updatedContact = \App\Modules\Contact\Domain\Contact::at(
                    $contactPayload['first_name'],
                    $contactPayload['last_name'],
                    $contactPayload['title'],
                    $contactPayload['email'],
                    $contactPayload['phone'] ?? ''
                );
                
                $contact->first_name = $updatedContact->first_name;
                $contact->last_name = $updatedContact->last_name;
                $contact->title = $updatedContact->title;
                $contact->email = $updatedContact->email;
                $contact->phone = $updatedContact->phone;
                
                $this->contactRepository->save($contact);
            }
            // Si solo viene 'id', no actualiza nada (reutiliza contact as-is)
        } else {
            // Caso 2: Crear nuevo Contact
            $contact = \App\Modules\Contact\Domain\Contact::at(
                $contactPayload['first_name'],
                $contactPayload['last_name'],
                $contactPayload['title'],
                $contactPayload['email'],
                $contactPayload['phone'] ?? ''
            );
            $this->contactRepository->save($contact);
        }

        // Validar ProgramState
        $programState = $this->programStateRepository->findById($programStateId);

        // Validar SDGs
        if (!empty($sdgIds)) {
            foreach ($sdgIds as $sdgId) {
                $this->sdgRepository->findById($sdgId);
            }
        }

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
        $program->contact_id = $contact->id;
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

}
