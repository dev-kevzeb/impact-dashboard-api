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

    }


    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    public function findProjectById(int $id)
    {
        $project = $this->projectRepository->findById($id);
        if (!$project) throw new \RuntimeException("The project with id {$id} does not exist.");
        
        return $project;
    }

    public function getProjectByName(string $name)
    {
        $project = $this->projectRepository->findByName($name);
        if (!$project) throw new \RuntimeException("The project with name {$name} does not exist.");
        
        return $project;
    }

    public function addIndicator(Indicator $indicator, Project $project){
        return $this->projectIndicatorService->createProjectIndicator($project['id'], $indicator['id']);
    }
    
    public function addDonors(Donor $donor, Project $project, float $contribution){
        return $this->projectDonorService->createProjectDonor($project['id'], $donor['id'], $contribution);
    }

    public function addAgencies(Agency $agency, Project $project, float $contribution)
    {
        return $this->projectAgencyService->createProjectAgency($project['id'], $agency['id'], $contribution);
    }


    public function syncIndicators(Project $project, array $indicators){
        $this->projectIndicatorService->deleteAllByProjectId($project['id']);

        foreach($indicators as $indicator){
            $founded = $this->indicatorService->getIndicatorById($indicator['id']);
            $this->addIndicator($founded, $project);
        }
    }

    public function syncDonors(Project $project, array $donors){
        $this->projectDonorService->deleteAllByProjectId($project['id']);

        foreach($donors as $donor){
            $founded = $this->donorService->getDonorById($donor['id']);
            $this->addDonors($founded, $project, $donor['contribution']);
        }
    }

    public function syncAgencies(Project $project, array $agencies){
        $this->projectAgencyService->deleteAllByProjectId($project['id']);

        foreach($agencies as $agency){
            $founded = $this->agencyService->getAgencyById($agency['id']);
            $this->addAgencies($founded, $project, $agency['contribution']);
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


        if(empty($program_id)) throw new \RuntimeException("The program id is required.");

        $normalizedName = preg_replace('/\s+/', ' ', trim($name));
        $capitalizedName = mb_convert_case($normalizedName, MB_CASE_TITLE, "UTF-8");
        
        $founded_project = $this->projectRepository->findByNameAndProgramId($program_id, $capitalizedName);

        if($founded_project) throw new \RuntimeException("The project with name $capitalizedName already exist in the selected program.");

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

        return $project;
    }

    public function updateProject( int $id, string $name, string $description, ?string $projectUrl, string $startDate, string $endDate, float $progress, string $comments, float $budget, array $contactPayload, int $beneficiaryId, int $projectStateId ): Project {

        $project = $this->findProjectById($id);

        if (!empty($contactPayload['id'])) {

            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) throw new \RuntimeException("Contact with id {$contactPayload['id']} does not exist.");
        } else {

            $contact = $project->contact;
            $contact->first_name = $contactPayload['first_name'];
            $contact->last_name = $contactPayload['last_name'];
            $contact->title = $contactPayload['title'];
            $contact->email = $contactPayload['email'];
            $contact->phone = $contactPayload['phone'] ?? "";

            $this->contactRepository->save($contact);
        }


        $beneficiary = $this->beneficiaryRepository->findById($beneficiaryId);
        if (!$beneficiary) throw new \RuntimeException("The beneficiary with id {$beneficiaryId} does not exist.");
        
        $projectState = $this->projectStateRepository->findById($projectStateId);
        if (!$projectState) throw new \RuntimeException("The project state with id {$projectStateId} does not exist.");

        $project->name = $name;
        $project->description = $description;
        $project->project_url = $projectUrl;
        $project->start_date = $startDate;
        $project->end_date = $endDate;
        $project->progress = $progress;
        $project->comments = $comments;
        $project->project_budget = $budget;

        $project->contact_id = $contact->id;
        $project->beneficiary_id = $beneficiary->id;
        $project->project_state_id = $projectState->id;

        $this->projectRepository->save($project);

        return $project;
    }
}
