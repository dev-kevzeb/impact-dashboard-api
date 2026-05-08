<?php

namespace App\Modules\Project\Service;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Agency\Service\AgencyService;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\Country\Service\CountryService;
use App\Modules\CountryKpa\Service\CountryKpaService;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Service\DonorService;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Indicator\Service\IndicatorService;
use App\Modules\Kpa\Service\KpaService;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\ProjectAgency\Service\ProjectAgencyService;
use App\Modules\ProjectIndicator\Service\ProjectIndicatorService;
use App\Modules\ProjectState\Repository\ProjectStateRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectDonor\Service\ProjectDonorService;
use App\Modules\ProjectInviteUser\Service\ProjectInviteUserService;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Repository\ProgramCountryUserRoleRepository;
use App\Modules\InviteProgram\Repository\InviteProgramRepository;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;
use App\Modules\StrategicOutput\Service\StrategicOutputService;
use App\Modules\Program\Service\ProgramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


class ProjectService
{
    private ProjectRepository $projectRepository;
    private ContactRepository $contactRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private ProjectStateRepository $projectStateRepository;
    private ProjectIndicatorService $projectIndicatorService;
    private IndicatorService $indicatorService;
    private CountryService $countryService;
    private CountryKpaService $countryKpaService;
    private KpaService $kpaService;
    private StrategicOutputService $strategicOutputService;
    private MeasureService $measureService;
    private ProgramCountryUserRoleRepository $programCountryUserRoleRepository;
    private InviteProgramRepository $inviteProgramRepository;
    private StrategicOutputRepository $strategicOutputRepository;

    private ProjectDonorService $projectDonorService;
    private DonorService $donorService;
    private ProjectAgencyService $projectAgencyService;
    private AgencyService $agencyService;
    private ProgramService $programService;
    private ProjectInviteUserService $projectInviteUserService;

    public function __construct(
        ProjectRepository $projectRepository,
        ContactRepository $contactRepository,
        BeneficiaryRepository $beneficiaryRepository,
        ProjectStateRepository $projectStateRepository,
        ProjectIndicatorService $projectIndicatorService,
        IndicatorService $indicatorService,
        ProjectDonorService $projectDonorService,
        DonorService $donorService,
        ProjectAgencyService $projectAgencyService,
        AgencyService $agencyService,
        ProgramService $programService,
        ProjectInviteUserService $projectInviteUserService,
        CountryService $countryService,
        CountryKpaService $countryKpaService,
        KpaService $kpaService,
        StrategicOutputService $strategicOutputService,
        MeasureService $measureService,
        ProgramCountryUserRoleRepository $programCountryUserRoleRepository,
        InviteProgramRepository $inviteProgramRepository,
        StrategicOutputRepository $strategicOutputRepository,
    ) {
        $this->projectRepository = $projectRepository;
        $this->contactRepository = $contactRepository;
        $this->beneficiaryRepository = $beneficiaryRepository;
        $this->projectStateRepository = $projectStateRepository;

        $this->projectIndicatorService = $projectIndicatorService;
        $this->indicatorService = $indicatorService;

        $this->projectDonorService = $projectDonorService;
        $this->donorService = $donorService;

        $this->projectAgencyService = $projectAgencyService;
        $this->agencyService = $agencyService;

        $this->countryService= $countryService;
        $this->countryKpaService = $countryKpaService;
        $this->kpaService = $kpaService;
        $this->strategicOutputService = $strategicOutputService;
        $this->measureService = $measureService;
        $this->programService = $programService;
        $this->projectInviteUserService = $projectInviteUserService;
        $this->programCountryUserRoleRepository = $programCountryUserRoleRepository;
        $this->inviteProgramRepository = $inviteProgramRepository;
        $this->strategicOutputRepository = $strategicOutputRepository;
    }


    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    public function findProjectById(int $id)
    {
        $project = $this->projectRepository->findById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");

        $this->projectInviteUserService->ensureCanViewProject($project);

        $project->load(['contact', 'beneficiary', 'projectState', 'donors', 'agencies', 'indicators.measure.strategicOutput.countryKpa.kpa']);
        $project->indicators->pluck('measure.strategicOutput.countryKpa.kpa')->filter()->unique('id')->each(fn($kpa) => $kpa->loadCount('strategicOutputs'));

        $this->projectInviteUserService->applyProjectAccess($project);

        return $project;
    }

    /**
     * Find a project by ID for public (unauthenticated) access.
     * Skips auth checks — only loads relations needed for the public detail page.
     */
    public function findPublicProjectById(int $id)
    {
        $project = $this->projectRepository->findById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");

        $project->load(['contact', 'beneficiary', 'projectState', 'donors', 'agencies', 'indicators.measure.strategicOutput.countryKpa.kpa']);

        return $project;
    }

    public function findProjectByProgramIdPaginated(int $programId, ?string $search, int $perPage)
    {
        $hasFullAccess = $this->projectInviteUserService->hasFullProgramAccess($programId);
        $hasCountryViewAccess = $this->projectInviteUserService->canViewProgramByCountry($programId);

        if ($hasFullAccess || $hasCountryViewAccess) {
            $projects = $this->projectRepository->getPaginatedProjectsByProgramId($programId, $search, $perPage);
        } else {
            $visibleProjectIds = $this->projectInviteUserService->getVisibleProjectIdsForProgram($programId);

            if (empty($visibleProjectIds)) {
                $projects = $this->projectRepository->emptyPaginated($perPage);
            } else {
                $projects = $this->projectRepository->paginateByIdsForProgram($programId, $visibleProjectIds, null, $search, $perPage);
            }
        }

        return $this->projectInviteUserService->applyProjectAccessToPaginator($projects);
    }

    public function getDashboardProjectsPaginated(?string $search, int $perPage): LengthAwarePaginator
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new \RuntimeException('Not authenticated.');
        }

        if ($user->hasPermissionTo('*:*')) {
            $projects = $this->projectRepository->getDashboardProjectsPaginated($search, $perPage);

            return $this->projectInviteUserService->applyProjectAccessToPaginator($projects);
        }

        if ($this->canViewProjectsByCountry($user)) {
            $countryIds = $user->userRoles()
                ->with('countries')
                ->get()
                ->flatMap(fn($userRole) => $userRole->countries->pluck('id'))
                ->unique()
                ->values()
                ->toArray();

            $projects = $this->projectRepository->getDashboardProjectsPaginatedByCountryIds($countryIds, $search, $perPage);

            return $this->projectInviteUserService->applyProjectAccessToPaginator($projects);
        }

        $userRoleIds = $user->userRoles()->pluck('id')->toArray();
        $countryUserRoleId = null;

        try {
            $countryUserRole = $user->getCountryUserRole();
            $countryUserRoleId = (int) $countryUserRole->id;
        } catch (\RuntimeException) {
            $countryUserRoleId = null;
        }

        $projects = $this->projectRepository->getDashboardProjectsPaginatedByUserRoleContext($userRoleIds, $countryUserRoleId, $search, $perPage);

        return $this->projectInviteUserService->applyProjectAccessToPaginator($projects);
    }

    private function canViewProjectsByCountry($user): bool
    {
        return $user->hasPermissionTo('projects:view_by_country') || $user->hasPermissionTo('programs:view_by_country');
    }

    public function getProjectByName(string $name)
    {
        $project = $this->projectRepository->findByName($name);
        if (!$project) throw new \RuntimeException("The project with name {$name} does not exist.");

        $this->projectInviteUserService->ensureCanViewProject($project);
        $this->projectInviteUserService->applyProjectAccess($project);

        return $project;
    }

    public function addIndicator(Indicator $indicator, Project $project)
    {
        return $this->projectIndicatorService->createProjectIndicator($project['id'], $indicator['id']);
    }

    public function addDonors(Donor $donor, Project $project, float $contribution)
    {
        return $this->projectDonorService->createProjectDonor($project['id'], $donor['id'], $contribution);
    }

    public function addAgencies(Agency $agency, Project $project, float $contribution)
    {
        return $this->projectAgencyService->createProjectAgency($project['id'], $agency['id'], $contribution);
    }

    private function strategicOutputBelongsToCountry(int $strategicOutputId, int $countryId): bool
    {
        return $this->strategicOutputRepository->belongsToCountry($strategicOutputId, $countryId);
    }

    private function resolveAccessibleProgramCountryId(int $programId): int
    {
        $user = auth('api')->user();
        if (!$user) {
            throw new \RuntimeException('Not authenticated.');
        }

        $authUserRoleIds = $user->userRoles()->pluck('id')->toArray();

        $ownerAssignment = $this->programCountryUserRoleRepository
            ->findFirstOwnedAssignmentByProgramAndUserRoleIds($programId, $authUserRoleIds);

        if ($ownerAssignment && $ownerAssignment->countryUserRole) {
            return (int) $ownerAssignment->countryUserRole->country_id;
        }

        if ($user->hasPermissionTo('*:*')) {
            $firstAssignment = $this->programCountryUserRoleRepository
                ->findFirstAssignmentByProgramId($programId);

            if ($firstAssignment && $firstAssignment->countryUserRole) {
                return (int) $firstAssignment->countryUserRole->country_id;
            }

            throw new \RuntimeException('Program has no country context assigned.');
        }

        $invite = $this->inviteProgramRepository
            ->findFirstInviteByProgramAndInvitedRoleIds($programId, $authUserRoleIds);

        if ($invite && $invite->programCountryUserRole && $invite->programCountryUserRole->countryUserRole) {
            return (int) $invite->programCountryUserRole->countryUserRole->country_id;
        }

        throw new \RuntimeException('You do not have access to this program context.');
    }

    private function ensureIndicatorsBelongToProgramCountry(int $programId, array $indicators): void
    {
        if (empty($indicators)) {
            return;
        }

        $user = auth('api')->user();
        if ($user && $user->hasPermissionTo('*:*')) {
            return;
        }

        $countryId = $this->resolveAccessibleProgramCountryId($programId);

        foreach ($indicators as $indicatorPayload) {
            $indicatorId = (int) ($indicatorPayload['id'] ?? 0);
            if ($indicatorId <= 0) {
                throw new \RuntimeException('Invalid indicator payload.');
            }

            $indicator = $this->indicatorService->getIndicatorById($indicatorId);
            $measure = $this->measureService->getMeasureById((int) $indicator->measure_id);

            if (!$this->strategicOutputBelongsToCountry((int) $measure->strategic_output_id, $countryId)) {
                throw new \RuntimeException('Selected indicators do not belong to the program country context.');
            }
        }
    }
    private function ensureUserCanEditProjectForCountry(): void
    {
        $user = auth('api')->user();
        if ($user && !$user->hasPermissionTo('*:*')) {
            try {
                $countryUserRole = $user->getCountryUserRole();
                $countryUserRole->load('country');
                if ($countryUserRole->country && !$countryUserRole->country->active) {
                    throw new \RuntimeException('Projects cannot be edited because your country is not active.');
                }
            } catch (\RuntimeException $e) {
                if (str_contains($e->getMessage(), 'Projects cannot be edited')) {
                    throw $e;
                }
            }
        }
    }

    private function ensureUserCanCreateProjectForProgram(int $programId): void
    {
        $this->resolveAccessibleProgramCountryId($programId);

        $user = auth('api')->user();
        if ($user && !$user->hasPermissionTo('*:*')) {
            try {
                $countryUserRole = $user->getCountryUserRole();
                $countryUserRole->load('country');
                if ($countryUserRole->country && !$countryUserRole->country->active) {
                    throw new \RuntimeException('Projects cannot be created because your country is not active.');
                }
            } catch (\RuntimeException $e) {
                if (str_contains($e->getMessage(), 'Projects cannot be created')) {
                    throw $e;
                }
            }
        }
    }

    private function ensureWeightWithinBounds(float $weight): void
    {
        if ($weight < 0 || $weight > 1) {
            throw new \RuntimeException('Project weight must be between 0 and 1.');
        }
    }

    private function ensureProgressWithinBounds(float $progress): void
    {
        if ($progress < 0 || $progress > 100) {
            throw new \RuntimeException('Project progress must be between 0 and 100.');
        }
    }

    public function getProgramKpasForCurrentUser(int $programId, ?string $search, int $perPage): LengthAwarePaginator
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);
        return $this->countryKpaService->getCountryKpasByCountryId($countryId, $search, $perPage);
    }

    public function getProgramStrategicOutputsForCurrentUser(int $programId, int $kpaId, ?string $search, int $perPage)
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);

        $countryKpas = $this->countryKpaService->getByCountryAndKpa($countryId, $kpaId);
        if ($countryKpas->isEmpty()) {
            throw new \RuntimeException('KPA not available for the program country context.');
        }

        $countryKpaIds = $countryKpas->pluck('id')->toArray();

        return $this->strategicOutputService->getStrategicOutputsByCountryKpaIds($countryKpaIds, $search, $perPage);
    }

    public function getProgramMeasuresForCurrentUser(int $programId, int $strategicOutputId, ?string $search, int $perPage)
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);

        if (!$this->strategicOutputBelongsToCountry($strategicOutputId, $countryId)) {
            throw new \RuntimeException('Strategic output not available for the program country context.');
        }

        return $this->measureService->getMeasuresByStrategicOutputId($strategicOutputId, $perPage, $search);
    }

    public function getProgramIndicatorsForCurrentUser(int $programId, int $measureId, ?string $search, int $perPage, ?array $exclude)
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);
        $measure = $this->measureService->getMeasureById($measureId);

        if (!$this->strategicOutputBelongsToCountry((int) $measure->strategic_output_id, $countryId)) {
            throw new \RuntimeException('Measure not available for the program country context.');
        }

        return $this->indicatorService->getIndicatorsByMeasureId($measureId, $perPage, $search, $exclude);
    }

    private function getProgramCountryKpasCollection(int $programId): Collection
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);
        $countryKpas = $this->countryKpaService->getCountryKpasByCountryId($countryId, null, -1);

        return $countryKpas->getCollection();
    }

    public function getProgramKpaNumberMap(int $programId): array
    {
        $kpas = $this->getProgramCountryKpasCollection($programId);
        $map = [];
        $index = 1;

        foreach ($kpas as $countryKpa) {
            $kpaId = $countryKpa->kpa?->id;
            if (!$kpaId || array_key_exists($kpaId, $map)) {
                continue;
            }

            $map[$kpaId] = $index;
            $index += 1;
        }

        return $map;
    }

    public function getProgramStrategicOutputNumberMap(int $programId, int $kpaId): array
    {
        $countryId = $this->resolveAccessibleProgramCountryId($programId);
        $countryKpas = $this->countryKpaService->getByCountryAndKpa($countryId, $kpaId);
        if ($countryKpas->isEmpty()) {
            return [];
        }

        $countryKpaIds = $countryKpas->pluck('id')->toArray();
        $strategicOutputs = $this->strategicOutputService->getByCountryKpaIds($countryKpaIds);

        $map = [];
        $index = 1;

        foreach ($strategicOutputs as $strategicOutput) {
            $map[$strategicOutput->id] = $index;
            $index += 1;
        }

        return $map;
    }

    public function getProgramMeasureNumberMap(int $strategicOutputId): array
    {
        $measures = $this->measureService->getByStrategicOutputIds([$strategicOutputId]);
        $map = [];
        $index = 1;

        foreach ($measures as $measure) {
            $map[$measure->id] = $index;
            $index += 1;
        }

        return $map;
    }

    public function getProgramStrategicOutputContext(int $programId, int $strategicOutputId): array
    {
        $strategicOutput = $this->strategicOutputService->getStrategicOutputById($strategicOutputId);
        $strategicOutput->loadMissing(['countryKpa.kpa']);

        $kpaId = (int) ($strategicOutput->countryKpa?->kpa?->id ?? 0);
        $kpaNumberMap = $kpaId > 0 ? $this->getProgramKpaNumberMap($programId) : [];
        $strategicOutputNumberMap = $kpaId > 0 ? $this->getProgramStrategicOutputNumberMap($programId, $kpaId) : [];

        $kpaNumber = $kpaId > 0 && array_key_exists($kpaId, $kpaNumberMap)
            ? (string) $kpaNumberMap[$kpaId]
            : '';

        $strategicOutputNumber = array_key_exists($strategicOutputId, $strategicOutputNumberMap)
            ? (string) $strategicOutputNumberMap[$strategicOutputId]
            : '';

        return [
            'kpa_id' => $kpaId,
            'kpa_number' => $kpaNumber,
            'strategic_output_number' => $strategicOutputNumber,
        ];
    }

    public function syncIndicators(Project $project, array $indicators)
    {
        $validated = [];
        foreach ($indicators as $indicator) {
            $validated[] = $this->indicatorService->getIndicatorById($indicator['id']);
        }
        $this->projectIndicatorService->deleteAllByProjectId($project->id);
        foreach ($validated as $indicator) {
            $this->addIndicator($indicator, $project);
        }
    }

    public function syncDonors(Project $project, array $donors)
    {
        $validated = [];
        foreach ($donors as $donor) {
            $validated[$donor['id']] = [
                'donor' => $this->donorService->getDonorById($donor['id']),
                'contribution' => $donor['contribution']
            ];
        }
        $this->projectDonorService->deleteAllByProjectId($project->id);
        foreach ($validated as $item) {
            $this->addDonors($item['donor'], $project, $item['contribution']);
        }
    }


    public function syncAgencies(Project $project, array $agencies)
    {
        $validated = [];
        foreach ($agencies as $agency) {
            $validated[$agency['id']] = [
                'agency' => $this->agencyService->getAgencyById($agency['id']),
                'contribution' => $agency['contribution']
            ];
        }

        $this->projectAgencyService->deleteAllByProjectId($project->id);

        foreach ($validated as $item) {
            $this->addAgencies($item['agency'], $project, $item['contribution']);
        }
    }

    public function createProject(
        int $program_id,
        string $name,
        string $description,
        ?string $projectUrl,
        string $startDate,
        string $endDate,
        float $progress,
        string $comments,
        float $budget,
        float $weight,
        array $indicators,
        array $donors,
        array $agencies,
        array $contact,
        array $beneficiary,
        array $projectState,
    ): Project {

        $this->ensureUserCanCreateProjectForProgram($program_id);
        $this->ensureIndicatorsBelongToProgramCountry($program_id, $indicators);
        $this->ensureWeightWithinBounds($weight);

        if (empty($program_id)) throw new \RuntimeException("The program id is required.");

        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        $capitalizedName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");

        $founded_project = $this->projectRepository->findByNameAndProgramId($program_id, $capitalizedName);

        if ($founded_project) throw new \RuntimeException("The project with name $capitalizedName already exist in the selected program.");

        $beneficiary = $this->beneficiaryRepository->findById($beneficiary['id']);
        if (!$beneficiary) throw new \RuntimeException("The beneficiary does not exist.");

        $projectState = $this->projectStateRepository->findById($projectState['id']);
        if (!$projectState) throw new \RuntimeException("The project state does not exist.");

        if (!empty($contact['id'])) {
            $contact = $this->contactRepository->findById($contact['id']);
            if (!$contact) throw new \RuntimeException("The contact with id {$contact['id']} does not exist.");
        } else {
            $contact = Contact::at(
                $contact['first_name'],
                $contact['last_name'],
                $contact['title'],
                $contact['email'],
                $contact['phone'] ?? ""
            );

            $this->contactRepository->save($contact);
        }

        $project = Project::at(
            $program_id,
            $capitalizedName,
            $description,
            $projectUrl,
            $startDate,
            $endDate,
            $progress,
            $comments,
            $budget,
            $weight,
            $contact,
            $beneficiary,
            $projectState
        );

        $project = $this->projectRepository->saveReturn($project);

        $this->attachCurrentCountryUserRoleToProject($project);

        $this->syncIndicators($project, $indicators);
        $this->syncDonors($project, $donors);
        $this->syncAgencies($project, $agencies);

        // Business Rule: Auto-activate program when first project is created
        $this->programService->activateProgramIfNeeded($program_id);

        return $project;
    }

    public function updateProjectWeight(int $id, float $weight): Project
    {
        $project = $this->findProjectById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");

        $this->ensureUserCanEditProjectForCountry();
        $this->ensureWeightWithinBounds($weight);
        $project->weight = $weight;

        // Virtual access flag is injected for API responses and must not be persisted.
        unset($project->can_edit);

        $this->projectRepository->save($project);

        return $project;
    }

    public function updateProjectProgress(int $id, float $progress): Project
    {
        $project = $this->findProjectById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");

        $this->projectInviteUserService->ensureCanEditProject($project);
        $this->ensureUserCanEditProjectForCountry();
        $this->ensureProgressWithinBounds($progress);

        $project->progress = $progress;

        // Virtual access flag is injected for API responses and must not be persisted.
        unset($project->can_edit);

        $this->projectRepository->save($project);

        return $project;
    }

    public function updateProject(int $id, int $program_id, string $name, string $description, ?string $projectUrl, string $startDate, string $endDate, float $progress, string $comments, float $budget, float $weight, array $indicators, array $donors, array $agencies,  array $contact, array $beneficiary, array $projectState): Project
    {
        if (empty($program_id)) throw new \RuntimeException("The program id is required.");

        $project = $this->findProjectById($id);
        if (empty($project)) throw new \RuntimeException("The project with id {$id} does not exist.");

        $this->projectInviteUserService->ensureCanEditProject($project);

        $this->ensureUserCanEditProjectForCountry();
        $this->ensureWeightWithinBounds($weight);

        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        $capitalizedName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");

        $founded_project = $this->projectRepository->findByNameAndProgramId($program_id, $capitalizedName);
        if ($founded_project && $founded_project->id !== $id) throw new \RuntimeException("The project with name $capitalizedName already exist in the selected program.");

        $beneficiary = $this->beneficiaryRepository->findById($beneficiary['id']);
        if (!$beneficiary) throw new \RuntimeException("The beneficiary does not exist.");

        $projectState = $this->projectStateRepository->findById($projectState['id']);
        if (!$projectState) throw new \RuntimeException("The project state does not exist.");

        $contact_founded = $this->contactRepository->findById($contact['id']);
        if (!$contact_founded) throw new \RuntimeException("The contact with id {$contact['id']} does not exist.");

        $contact_founded->first_name = $contact['first_name'];
        $contact_founded->last_name = $contact['last_name'];
        $contact_founded->title = $contact['title'];
        $contact_founded->email = $contact['email'];
        $contact_founded->phone = $contact['phone'] ?? "";

        $this->contactRepository->save($contact_founded);

        $project->program_id = $program_id;
        $project->name = $capitalizedName;
        $project->description = $description;
        $project->project_url = $projectUrl;
        $project->start_date = $startDate;
        $project->end_date = $endDate;
        $project->progress = $progress;
        $project->comments = $comments;
        $project->project_budget = $budget;
        $project->weight = $weight;

        $project->contact_id = $contact_founded->id;
        $project->beneficiary_id = $beneficiary->id;
        $project->project_state_id = $projectState->id;

        // Virtual access flag injected for API responses must never be persisted.
        unset($project->can_edit);

        $this->projectRepository->save($project);

        $this->syncIndicators($project, $indicators);
        $this->syncDonors($project, $donors);
        $this->syncAgencies($project, $agencies);

        return $project;
    }

    public function deleteProject(int $id): void
    {
        $project = $this->findProjectById($id);

        $this->projectInviteUserService->ensureCanEditProject($project);

        $this->resolveAccessibleProgramCountryId((int) $project->program_id);

        DB::transaction(function () use ($project): void {
            $programId = (int) $project->program_id;
            $contactId = $project->contact_id ? (int) $project->contact_id : null;

            $project->delete();

            if ($contactId !== null) {
                $usedByAnotherProject = Project::query()
                    ->where('contact_id', $contactId)
                    ->exists();

                $usedByProgram = Program::query()
                    ->where('contact_id', $contactId)
                    ->exists();

                if (!$usedByAnotherProject && !$usedByProgram) {
                    $contact = $this->contactRepository->findOneBy('id', $contactId);
                    if ($contact) {
                        $contact->delete();
                    }
                }
            }

            $this->programService->activateProgramIfNeeded($programId);
        });
    }

    private function getCountryKpaIds(array $filters)
    {
        $countryKpaIds = collect();

        if ($countryId = data_get($filters, 'country.id')) {
            if ($kpaId = data_get($filters, 'kpa.id')) $countryKpaIds = $this->countryKpaService->getByCountryAndKpa($countryId, $kpaId)->pluck('id');
            else $countryKpaIds = $this->countryKpaService->getByCountry($countryId)->pluck('id');
        }

        return $countryKpaIds;
    }

    private function getStrategicOutputIds(array $filters, Collection $countryKpaIds)
    {
        if ($strategicOutputId = data_get($filters, 'strategic_output.id')) return collect([$strategicOutputId]);
        if ($countryKpaIds->isNotEmpty()) return $this->strategicOutputService->getByCountryKpaIds($countryKpaIds->toArray())->pluck('id');
        
        return collect();
    }

    private function getMeasureIds(array $filters, Collection $strategicOutputIds)
    {
        if ($measureId = data_get($filters, 'measure.id')) return collect([$measureId]);
        if ($strategicOutputIds->isNotEmpty()) return $this->measureService->getByStrategicOutputIds($strategicOutputIds->toArray())->pluck('id');
        
        return collect();
    }

    private function getIndicatorIds(Collection $measureIds)
    {
        if ($measureIds->isNotEmpty()) return $this->indicatorService->getByMeasureIds($measureIds->toArray())->pluck('id');
        return collect();
    }

    private function attachCurrentCountryUserRoleToProject(Project $project): void
    {
        $user = auth('api')->user();
        if (!$user) {
            return;
        }

        try {
            $countryUserRole = $user->getCountryUserRole();
        } catch (\RuntimeException $e) {
            return;
        }

        $this->projectInviteUserService->attachProject($project, $countryUserRole);
    }

    public function getPublicProjects(array $filters, ?string $search, int $perPage, string $sort = 'date_newest')
    {
        $programId = data_get($filters, 'program_id');
        $hasAnyFilter = data_get($filters, 'country.id') || data_get($filters, 'kpa.id') || data_get($filters, 'strategic_output.id') || data_get($filters, 'measure.id') || data_get($filters, 'project_state.id');
        if (!$hasAnyFilter) {
            if ($programId !== null) return $this->projectRepository->getPaginatedForProgram((int) $programId, $search, $perPage, $sort);
            return $this->projectRepository->getPaginated($search, $perPage, $sort);
        }
        
        $hasHierarchyFilters = data_get($filters, 'country.id') || data_get($filters, 'kpa.id') || data_get($filters, 'strategic_output.id') || data_get($filters, 'measure.id');

        $projectStateId = data_get($filters, 'project_state.id');

        if (!$hasHierarchyFilters) {
            if ($programId !== null) return $this->projectRepository->getPaginatedByStateForProgram((int) $programId, $projectStateId, $search, $perPage, $sort);
            return $this->projectRepository->getPaginatedByState($projectStateId,$search,$perPage, $sort);
        }

        $countryKpaIds = $this->getCountryKpaIds($filters);
        $strategicOutputIds = $this->getStrategicOutputIds($filters, $countryKpaIds);

        $measureIds = $this->getMeasureIds($filters, $strategicOutputIds);
        $indicatorIds = $this->getIndicatorIds($measureIds);

        if ($indicatorIds->isEmpty()) return $this->projectRepository->emptyPaginated($perPage);
    
        $projectIds = $this->projectIndicatorService->getProjectIdsByIndicatorIds($indicatorIds->unique()->values()->toArray());

        if ($programId !== null) return $this->projectRepository->paginateByIdsForProgram((int) $programId, $projectIds, $projectStateId, $search, $perPage, $sort);
        return $this->projectRepository->paginateByIds($projectIds, $projectStateId, $search, $perPage, $sort);
    }
}