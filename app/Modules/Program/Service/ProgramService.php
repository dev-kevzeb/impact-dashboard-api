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
     * Create a new program
     */
    public function createProgram(
        string $name,
        string $description,
        ?string $bannerImg,
        string $programUrl,
        array $contactPayload,
        array $sdgIds = []
    ): Program {
        // Validate duplicates
        if ($this->programRepository->exists('name', trim($name))) {
            throw new RuntimeException("A program with the name already exists: {$name}");
        }

        // Handle Contact (new or existing)
        if (!empty($contactPayload['id'])) {
            // Case 1: Existing Contact
            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) {
                throw new RuntimeException("The contact with id {$contactPayload['id']} does not exist.");
            }
        } else {
            // Case 2: Create new Contact
            $contact = \App\Modules\Contact\Domain\Contact::at(
                $contactPayload['first_name'],
                $contactPayload['last_name'],
                $contactPayload['title'],
                $contactPayload['email'],
                $contactPayload['phone'] ?? ''
            );
            $this->contactRepository->save($contact);
        }

        // Force "Inactive" state when creating (business rule)
        // Will only change to "Active" when it has associated projects
        $inactiveState = $this->programStateRepository->findBy('name', 'Inactive');

        // Validate SDGs if they exist
        if (!empty($sdgIds)) {
            foreach ($sdgIds as $sdgId) {
                $this->sdgRepository->findById($sdgId);
            }
        }

        // Use the found Inactive state
        $programState = $inactiveState;

        // Create program using factory method with objects
        $program = Program::at(
            $name,
            $description,
            $bannerImg,
            $programUrl,
            $contact,
            $programState
        );

        // Save to database
        $this->programRepository->save($program);

        // Synchronize M:N relationships
        if (!empty($sdgIds)) {
            $this->programRepository->syncSdgs($program, $sdgIds);
        }

        return $program->fresh(['contact', 'programState', 'sdgs']);
    }

    /**
     * Get program by ID
     */
    public function getProgramById(int $id): Program
    {
        return $this->programRepository->findByIdWithRelations($id);
    }

    /**
     * Get all programs with pagination
     * @param int $perPage Number of records per page (default: 10)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPrograms(int $perPage = 10)
    {
        return $this->programRepository->paginateWithRelations($perPage);
    }

    /**
     * Update a program
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
        // Get existing program
        $program = $this->programRepository->findById($id);

        // Validate duplicates (except current)
        $existing = $this->programRepository->exists('name', trim($name));
        if ($existing && strtolower(trim($program->name)) !== strtolower(trim($name))) {
            throw new RuntimeException("A program with the name already exists: {$name}");
        }

        // Handle Contact (new, existing or update)
        if (!empty($contactPayload['id'])) {
            // Case 1: Existing Contact - search
            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) {
                throw new RuntimeException("The contact with id {$contactPayload['id']} does not exist.");
            }

            // If additional data comes, UPDATE the contact
            if (
                isset($contactPayload['first_name']) && isset($contactPayload['last_name'])
                && isset($contactPayload['title']) && isset($contactPayload['email'])
            ) {

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
            // If only 'id' comes, doesn't update anything (reuse contact as-is)
        } else {
            // Case 2: Create new Contact
            $contact = \App\Modules\Contact\Domain\Contact::at(
                $contactPayload['first_name'],
                $contactPayload['last_name'],
                $contactPayload['title'],
                $contactPayload['email'],
                $contactPayload['phone'] ?? ''
            );
            $this->contactRepository->save($contact);
        }

        // Validate ProgramState
        $programState = $this->programStateRepository->findById($programStateId);

        // Validate SDGs
        if (!empty($sdgIds)) {
            foreach ($sdgIds as $sdgId) {
                $this->sdgRepository->findById($sdgId);
            }
        }

        // Validate data with factory method (without saving)
        Program::at(
            $name,
            $description,
            $bannerImg,
            $programUrl,
            $contact,
            $programState
        );

        // Update fields
        $program->name = trim($name);
        $program->description = trim($description);
        $program->banner_img = $bannerImg ? trim($bannerImg) : $program->banner_img;
        $program->program_url = trim($programUrl);
        $program->contact_id = $contact->id;
        $program->program_state_id = $programStateId;

        // Save changes
        $this->programRepository->save($program);

        // Synchronize M:N relationships
        $this->programRepository->syncSdgs($program, $sdgIds);

        return $program->fresh(['contact', 'programState', 'sdgs']);
    }

    /**
     * Search program by name
     */
    public function findProgramByName(string $name): Program
    {
        return $this->programRepository->findBy('name', $name);
    }

    /**
     * Activate or deactivate program based on projects count
     * Business Rule: Programs auto-activate when they have projects, auto-deactivate when empty
     * 
     * @param int $programId
     * @return void
     * @throws RuntimeException
     */
    public function activateProgramIfNeeded(int $programId): void
    {
        $program = $this->programRepository->findById($programId);
        if (!$program) {
            throw new RuntimeException("Program with id {$programId} does not exist.");
        }

        // Count projects associated with this program
        $projectsCount = $program->projects()->count();

        // Get current state name
        $currentState = $program->programState->name;

        // Business Rule 1: If has projects and is Inactive → Activate
        if ($projectsCount > 0 && strtolower($currentState) === 'inactive') {
            $activeState = $this->programStateRepository->findBy('name', 'Active');
            if ($activeState) {
                $program->program_state_id = $activeState->id;
                $this->programRepository->save($program);
            }
        }

        // Business Rule 2: If no projects and is Active → Deactivate
        if ($projectsCount === 0 && strtolower($currentState) === 'active') {
            $inactiveState = $this->programStateRepository->findBy('name', 'Inactive');
            if ($inactiveState) {
                $program->program_state_id = $inactiveState->id;
                $this->programRepository->save($program);
            }
        }
    }
}
