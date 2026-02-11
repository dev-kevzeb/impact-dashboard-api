<?php

namespace App\Modules\Project\Service;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Agency\Service\AgencyService;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Donor\Service\DonorService;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Indicator\Service\IndicatorService;
use App\Modules\ProjectAgency\Service\ProjectAgencyService;
use App\Modules\ProjectIndicator\Service\ProjectIndicatorService;
use App\Modules\ProjectState\Repository\ProjectStateRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\Project\Domain\Project;
use App\Modules\ProjectDonor\Service\ProjectDonorService;
use App\Modules\Program\Service\ProgramService;


class ProjectService
{
    private ProjectRepository $projectRepository;
    private ContactRepository $contactRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private ProjectStateRepository $projectStateRepository;
    private ProjectIndicatorService $projectIndicatorService;
    private IndicatorService $indicatorService;
    private ProjectDonorService $projectDonorService;
    private DonorService $donorService;
    private ProjectAgencyService $projectAgencyService;
    private AgencyService $agencyService;
    private ProgramService $programService;

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
        ProgramService $programService
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
        $this->programService = $programService;
    }


    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    public function findProjectById(int $id)
    {
        $project = $this->projectRepository->findById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");
        $project->load(['contact', 'beneficiary', 'projectState', 'donors', 'agencies', 'indicators.measure.strategicOutput.countryKpa.kpa']);
        $project->indicators->pluck('measure.strategicOutput.countryKpa.kpa')->filter()->unique('id')->each(fn($kpa) => $kpa->loadCount('strategicOutputs'));

        return $project;
    }

    public function findProjectByProgramIdPaginated(int $programId, ?string $search, int $perPage)
    {
        return $this->projectRepository->getPaginatedProjectsByProgramId($programId, $search, $perPage);
    }

    public function getProjectByName(string $name)
    {
        $project = $this->projectRepository->findByName($name);
        if (!$project) throw new \RuntimeException("The project with name {$name} does not exist.");

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
        array $indicators,
        array $donors,
        array $agencies,
        array $contact,
        array $beneficiary,
        array $projectState,
    ): Project {


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
            $contact,
            $beneficiary,
            $projectState
        );

        $project = $this->projectRepository->saveReturn($project);

        $this->syncIndicators($project, $indicators);
        $this->syncDonors($project, $donors);
        $this->syncAgencies($project, $agencies);

        // Business Rule: Auto-activate program when first project is created
        $this->programService->activateProgramIfNeeded($program_id);

        return $project;
    }

    public function updateProject(int $id, int $program_id, string $name, string $description, ?string $projectUrl, string $startDate, string $endDate, float $progress, string $comments, float $budget, array $indicators, array $donors, array $agencies,  array $contact, array $beneficiary, array $projectState): Project
    {
        if (empty($program_id)) throw new \RuntimeException("The program id is required.");

        $project = $this->findProjectById($id);
        if (empty($project)) throw new \RuntimeException("The project with id {$id} does not exist.");

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

        $project->contact_id = $contact_founded->id;
        $project->beneficiary_id = $beneficiary->id;
        $project->project_state_id = $projectState->id;

        $this->projectRepository->save($project);

        $this->syncIndicators($project, $indicators);
        $this->syncDonors($project, $donors);
        $this->syncAgencies($project, $agencies);

        return $project;
    }
}
