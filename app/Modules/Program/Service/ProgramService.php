<?php

namespace App\Modules\Program\Service;

use App\Modules\Program\Domain\Program;
use App\Modules\Program\Repository\ProgramRepository;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectInviteUser\Repository\ProjectInviteUserRepository;
use App\Modules\ProgramState\Repository\ProgramStateRepository;
use App\Modules\Sdg\Repository\SdgRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProgramService
{
    private ProgramRepository $programRepository;
    private ContactRepository $contactRepository;
    private ProgramStateRepository $programStateRepository;
    private SdgRepository $sdgRepository;
    private ProjectInviteUserRepository $projectInviteUserRepository;

    public function __construct(
        ProgramRepository $programRepository,
        ContactRepository $contactRepository,
        ProgramStateRepository $programStateRepository,
        SdgRepository $sdgRepository,
        ProjectInviteUserRepository $projectInviteUserRepository
    ) {
        $this->programRepository = $programRepository;
        $this->contactRepository = $contactRepository;
        $this->programStateRepository = $programStateRepository;
        $this->sdgRepository = $sdgRepository;
        $this->projectInviteUserRepository = $projectInviteUserRepository;
    }

    private function ensureUserCountryIsActive(string $verb): void
    {
        $user = auth('api')->user();
        if ($user && !$user->hasPermissionTo('*:*')) {
            try {
                $countryUserRole = $user->getCountryUserRole();
                $countryUserRole->load('country');
                if ($countryUserRole->country && !$countryUserRole->country->active) {
                    throw new RuntimeException("Programs cannot be {$verb} because your country is not active.");
                }
            } catch (RuntimeException $e) {
                if (str_contains($e->getMessage(), 'Programs cannot be')) {
                    throw $e;
                }
            }
        }
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
        $this->ensureUserCountryIsActive('created');

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

        // Synchronize M:N relationships (always required, at least 1 SDG)
        $this->programRepository->syncSdgs($program, $sdgIds);

        return $program->fresh(['contact', 'programState', 'sdgs']);
    }

    /**
     * Get program by ID
     */
    public function getProgramById(int $id): Program
    {
        $program = $this->programRepository->findByIdWithRelations($id);

        // Load project relationships required for public/private detail aggregates.
        $program->loadMissing([
            'projects.beneficiary',
            'projects.donors',
            'projects.agencies',
            'projects.indicators.measure.StrategicOutput.countryKpa.country',
        ]);

        $program->setAttribute('program_summary', $this->buildProgramSummary($program));

        return $program;
    }

    /**
     * Build aggregated detail data from all projects assigned to the program.
     */
    private function buildProgramSummary(Program $program): array
    {
        $projects = $program->projects ?? collect();

        $startDate = $projects
            ->pluck('start_date')
            ->filter()
            ->sort()
            ->first();

        $endDate = $projects
            ->pluck('end_date')
            ->filter()
            ->sortDesc()
            ->first();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'geographical_focus' => $this->extractGeographicalFocus($projects),
            'beneficiaries' => $this->extractUniqueBeneficiaries($projects),
            'status' => $program->programState?->name,
            'donors' => $this->extractUniqueDonors($projects),
            'budget' => (float) $projects->sum(fn($project) => (float) ($project->project_budget ?? 0)),
            'implementing_agencies' => $this->extractUniqueAgencies($projects),
            'contact_person' => [
                'id' => $program->contact?->id,
                'first_name' => $program->contact?->first_name,
                'last_name' => $program->contact?->last_name,
                'title' => $program->contact?->title,
                'email' => $program->contact?->email,
                'phone' => $program->contact?->phone,
            ],
        ];
    }

    private function extractGeographicalFocus(Collection $projects): array
    {
        return $projects
            ->flatMap(function ($project) {
                return ($project->indicators ?? collect())
                    ->map(function ($indicator) {
                        $country = $indicator->measure?->StrategicOutput?->countryKpa?->country;

                        if (!$country) {
                            return null;
                        }

                        return [
                            'id' => $country->id,
                            'name' => $country->name,
                        ];
                    });
            })
            ->filter()
            ->unique('id')
            ->values()
            ->all();
    }

    private function extractUniqueBeneficiaries(Collection $projects): array
    {
        return $projects
            ->map(function ($project) {
                if (!$project->beneficiary) {
                    return null;
                }

                return [
                    'id' => $project->beneficiary->id,
                    'name' => $project->beneficiary->name,
                ];
            })
            ->filter()
            ->unique('id')
            ->values()
            ->all();
    }

    private function extractUniqueDonors(Collection $projects): array
    {
        return $projects
            ->flatMap(fn($project) => $project->donors ?? collect())
            ->map(fn($donor) => [
                'id' => $donor->id,
                'name' => $donor->name,
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function extractUniqueAgencies(Collection $projects): array
    {
        return $projects
            ->flatMap(fn($project) => $project->agencies ?? collect())
            ->map(fn($agency) => [
                'id' => $agency->id,
                'name' => $agency->name,
                'url' => $agency->url,
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function buildPrivateProgramSummary(Collection $projects): array
    {
        return [
            'donors' => $this->extractUniqueDonors($projects),
            'implementing_agencies' => $this->extractUniqueAgencies($projects),
            'budget' => (float) $projects->sum(fn($project) => (float) ($project->project_budget ?? 0)),
        ];
    }

    /**
     * Get all programs with pagination (all countries).
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPrograms(int $perPage = 10)
    {
        return $this->programRepository->paginateWithRelations($perPage);
    }

    public function getAccessibleProgramsForCurrentUser(int $perPage = 10, ?string $search = null)
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if ($user->hasPermissionTo('*:*')) {
            $programs = $this->programRepository->paginateWithRelations($perPage);
            $programs->getCollection()->transform(function ($program) {
                $program->setAttribute('can_edit', 1);
                return $program;
            });

            return $this->applyVisibleProjectsCount($programs, $user);
        }

        if ($user->hasPermissionTo('programs:view_by_country')) {
            $countryIds = $user->userRoles()
                ->with('countries')
                ->get()
                ->flatMap(fn($userRole) => $userRole->countries->pluck('id'))
                ->unique()
                ->values()
                ->toArray();

            $programs = $this->programRepository->paginateByCountryIds($countryIds, $perPage, $search);

            return $this->applyVisibleProjectsCount($programs, $user);
        }

        $userRoleIds = $user->userRoles()->pluck('id')->toArray();
        $programs = $this->programRepository->paginateAccessibleByUserRoleIds($userRoleIds, $perPage, $search);

        return $this->applyVisibleProjectsCount($programs, $user);
    }

    private function applyVisibleProjectsCount($programs, $user)
    {
        $countryUserRole = null;

        $programs->getCollection()->transform(function ($program) use ($user, &$countryUserRole) {
            if ($user->hasPermissionTo('*:*') || $user->hasPermissionTo('programs:view_by_country') || (bool) ($program->can_edit ?? false)) {
                $program->setAttribute('visible_projects_count', (int) ($program->projects_count ?? 0));
                $program->setAttribute(
                    'program_summary',
                    $this->buildPrivateProgramSummary(
                        Project::query()
                            ->where('program_id', $program->id)
                            ->with(['donors', 'agencies'])
                            ->get(['id', 'program_id', 'project_budget'])
                    )
                );

                return $program;
            }

            if (!$countryUserRole) {
                try {
                    $countryUserRole = $user->getCountryUserRole();
                } catch (RuntimeException) {
                    $program->setAttribute('visible_projects_count', 0);

                    return $program;
                }
            }

            $program->setAttribute(
                'visible_projects_count',
                $this->projectInviteUserRepository->countProjectsByProgramAndCountryUserRole(
                    (int) $program->id,
                    (int) $countryUserRole->id
                )
            );

            $projectIds = $this->projectInviteUserRepository->getProjectIdsByProgramAndCountryUserRole(
                (int) $program->id,
                (int) $countryUserRole->id
            );

            $visibleProjects = empty($projectIds)
                ? collect()
                : Project::query()
                    ->whereIn('id', $projectIds)
                    ->with(['donors', 'agencies'])
                    ->get(['id', 'program_id', 'project_budget']);

            $program->setAttribute('program_summary', $this->buildPrivateProgramSummary($visibleProjects));

            return $program;
        });

        return $programs;
    }

    /**
     * Get public programs with the same hierarchy filters used in progress
     */
    public function getPublicPrograms(array $filters, ?string $search, int $perPage, string $sort = 'date_newest')
    {
        $hasAnyFilter =
            data_get($filters, 'country.id') ||
            data_get($filters, 'kpa.id') ||
            data_get($filters, 'strategic_output.id') ||
            data_get($filters, 'measure.id') ||
            data_get($filters, 'program_state.id');

        if (!$hasAnyFilter) {
            return $this->programRepository->getPaginated($search, $perPage, $sort);
        }

        return $this->programRepository->getPublicPaginated($filters, $search, $perPage, $sort);
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
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if (!$user->hasPermissionTo('*:*')) {
            $userRoleIds = $user->userRoles()->pluck('id')->toArray();
            if (!$this->programRepository->isEditableByUserRoleIds($id, $userRoleIds)) {
                throw new RuntimeException('You do not have permission to edit this program.');
            }

            $this->ensureUserCountryIsActive('edited');
        }

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
     * Delete a program and its cascading relationships.
     * Blocks if the program still has associated projects.
     */
    public function deleteProgram(int $id): void
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new RuntimeException('Not authenticated.');
        }

        if (!$user->hasPermissionTo('*:*')) {
            $userRoleIds = $user->userRoles()->pluck('id')->toArray();
            if (!$this->programRepository->isEditableByUserRoleIds($id, $userRoleIds)) {
                throw new RuntimeException('You do not have permission to delete this program.');
            }
        }

        $program = $this->programRepository->findById($id);

        if ($program->projects()->count() > 0) {
            throw new RuntimeException('Cannot delete a program that still has associated projects.');
        }

        $contactId = $program->contact_id ? (int) $program->contact_id : null;
        $bannerImg = $program->banner_img;

        DB::transaction(function () use ($program, $contactId): void {
            $program->delete();

            if ($contactId !== null) {
                $usedByAnotherProgram = Program::query()->where('contact_id', $contactId)->exists();
                $usedByProject = Project::query()->where('contact_id', $contactId)->exists();
                if (!$usedByAnotherProgram && !$usedByProject) {
                    $contact = $this->contactRepository->findOneBy('id', $contactId);
                    if ($contact) {
                        $contact->delete();
                    }
                }
            }
        });

        if ($bannerImg) {
            Storage::disk('public')->delete($bannerImg);
        }
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

        $projectsCount = $program->projects()->count();

        $currentState = $program->programState->name;

        if ($projectsCount > 0 && strtolower($currentState) === 'inactive') {
            $activeState = $this->programStateRepository->findBy('name', 'Active');
            if ($activeState) {
                $program->program_state_id = $activeState->id;
                $this->programRepository->save($program);
            }
        }

        if ($projectsCount === 0 && strtolower($currentState) === 'active') {
            $inactiveState = $this->programStateRepository->findBy('name', 'Inactive');
            if ($inactiveState) {
                $program->program_state_id = $inactiveState->id;
                $this->programRepository->save($program);
            }
        }
    }
}
