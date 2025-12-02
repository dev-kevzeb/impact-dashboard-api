<?php

namespace App\Modules\Project\Service;

use App\Modules\Contact\Domain\Contact;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\ProjectState\Repository\ProjectStateRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\Project\Domain\Project;

class ProjectService
{
    private ProjectRepository $projectRepository;
    private ContactRepository $contactRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private ProjectStateRepository $projectStateRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        ContactRepository $contactRepository,
        BeneficiaryRepository $beneficiaryRepository,
        ProjectStateRepository $projectStateRepository
    ) {
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
        if (!$project) {
            throw new \RuntimeException("El proyecto con id {$id} no existe.");
        }

        return $project;
    }

    public function getProjectByName(string $name)
    {
        $project = $this->projectRepository->findByName($name);
        if (!$project) {
            throw new \RuntimeException("El proyecto con nombre {$name} no existe.");
        }

        return $project;
    }

    public function createProject(
        string $name,
        string $description,
        ?string $projectUrl,
        string $startDate,
        string $endDate,
        float $progress,
        string $comments,
        float $budget,
        array $contactPayload,
        int $beneficiaryId,
        int $projectStateId
    ): Project {

        if (!empty($contactPayload['id'])) {
            
            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) throw new \RuntimeException("El contacto con id {$contactPayload['id']} no existe.");
            
        } else {

            $existingContact = $this->contactRepository->findOneBy('email', $contactPayload['email']);

            if ($existingContact) throw new \RuntimeException("Ya existe un contacto registrado con el email {$contactPayload['email']}.");

            $contact = Contact::at(
                $contactPayload['first_name'],
                $contactPayload['last_name'],
                $contactPayload['title'],
                $contactPayload['email'],
                $contactPayload['phone'] ?? ""
            );

            $this->contactRepository->save($contact);
        }

        $beneficiary = $this->beneficiaryRepository->findById($beneficiaryId);
        if (!$beneficiary) throw new \RuntimeException("El beneficiario con id {$beneficiaryId} no existe.");

        $projectState = $this->projectStateRepository->findById($projectStateId);
        if (!$projectState) throw new \RuntimeException("El estado del proyecto con id {$projectStateId} no existe.");
        
        $project = Project::at(
            $name,
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

        $this->projectRepository->save($project);

        return $project;
    }

    public function updateProject( int $id, string $name, string $description, ?string $projectUrl, string $startDate, string $endDate, float $progress, string $comments, float $budget, array $contactPayload, int $beneficiaryId, int $projectStateId ): Project {

        $project = $this->findProjectById($id);

        if (!empty($contactPayload['id'])) {

            $contact = $this->contactRepository->findById($contactPayload['id']);
            if (!$contact) {
                throw new \RuntimeException("El contacto con id {$contactPayload['id']} no existe.");
            }

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
        if (!$beneficiary) throw new \RuntimeException("El beneficiario con id {$beneficiaryId} no existe.");
        
        $projectState = $this->projectStateRepository->findById($projectStateId);
        if (!$projectState) throw new \RuntimeException("El estado del proyecto con id {$projectStateId} no existe.");

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
