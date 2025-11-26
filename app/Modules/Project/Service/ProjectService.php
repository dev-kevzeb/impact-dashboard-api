<?php

namespace App\Modules\Project\Service;

use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Project\Domain\Project;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectState\Repository\ProjectStateRepository;



class ProjectService{

    private ProjectRepository $projectRepository;

    private ContactRepository $contactRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private ProjectStateRepository $projectStateRepository;


    public function __construct(ProjectRepository $projectRepository, ContactRepository $contactRepository, BeneficiaryRepository $beneficiaryRepository, ProjectStateRepository $projectStateRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->contactRepository = $contactRepository;
        $this->beneficiaryRepository = $beneficiaryRepository;
        $this->projectStateRepository = $projectStateRepository;
    }

    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }
    
    public function findProjectById(int $id)
    {
        $project = $this->projectRepository->findById($id);
        if(empty($project)) throw new \RuntimeException("El proyecto con id {$id} no existe.");
        return $project;
    }

    public function createProject(string $name, string $description, string $projectUrl = null, string $startDate, string $endDate, float $progress, string $comments, float $budget, $contact_id, $beneficiary_id, $projectState_id): Project
    {
        $contact = $this->contactRepository->findById($contact_id);
        if(empty($contact)) throw new \RuntimeException("El contacto con id {$contact_id} no existe.");
        $beneficiary = $this->beneficiaryRepository->findById($beneficiary_id);
        if(empty($beneficiary)) throw new \RuntimeException("El beneficiario con id {$beneficiary_id} no existe.") ;
        $projectState = $this->projectStateRepository->findById($projectState_id);
        if(empty($projectState)) throw new \RuntimeException("El estado del proyecto con id {$projectState_id} no existe.") ;

        $project = Project::at($name, $description, $projectUrl, $startDate, $endDate, $progress, $comments, $budget, $contact, $beneficiary, $projectState);
        $this->projectRepository->save(entity: $project);
        return $project;
    }

    public function getProjectByName(string $name)
    {
        $project = $this->projectRepository->findBy('name', $name);
        if(empty($project)) throw new \RuntimeException("El proyecto con nombre {$name} no existe.");
        return $project;
    }

    public function updateProject(int $id, string $name, string $description, string $projectUrl, string $startDate, string $endDate, string $progress, string $comments, string $budget, string $contact, string $beneficiary, string $projectState): Project
    {   
        $project = $this->projectRepository->findById($id);
        $updateProject = Project::at($name, $description, $projectUrl, $startDate, $endDate, $progress, $comments, $budget, $contact, $beneficiary, $projectState);
        $project->name = $updateProject->name;
        $project->description = $updateProject->description;
        $project->project_url = $updateProject->project_url;
        $project->startDate = $updateProject->startDate;
        $project->endDate = $updateProject->endDate;
        $project->comments = $comments;
        $project->budget = $budget;
        $project->contact = $contact;
        $project->beneficiary = $beneficiary;
        $project->projectState = $projectState;

        $this->projectRepository->save($project);
        return $project;
    }
}