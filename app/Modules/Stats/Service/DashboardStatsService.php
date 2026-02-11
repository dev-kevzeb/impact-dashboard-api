<?php

namespace App\Modules\Stats\Service;

use App\Modules\Program\Repository\ProgramRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\Donor\Repository\DonorRepository;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\User\Repository\UserRepository;
use App\Modules\Project\Domain\Project;

class DashboardStatsService
{
    private ProgramRepository $programRepository;
    private ProjectRepository $projectRepository;
    private DonorRepository $donorRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private UserRepository $userRepository;

    public function __construct(
        ProgramRepository $programRepository,
        ProjectRepository $projectRepository,
        DonorRepository $donorRepository,
        BeneficiaryRepository $beneficiaryRepository,
        UserRepository $userRepository
    ) {
        $this->programRepository = $programRepository;
        $this->projectRepository = $projectRepository;
        $this->donorRepository = $donorRepository;
        $this->beneficiaryRepository = $beneficiaryRepository;
        $this->userRepository = $userRepository;
    }

    public function getStats(): array
    {
        return [
            'total_programs' => $this->programRepository->count(),
            'total_projects' => $this->projectRepository->count(),
            'total_donors' => $this->donorRepository->count(),
            'total_beneficiaries' => $this->beneficiaryRepository->count(),
            'total_users' => $this->userRepository->count(),
            'active_projects' => $this->getActiveProjectsCount(),
            'completed_projects' => $this->getCompletedProjectsCount(),
        ];
    }

    public function getProgramsByState(): array
    {
        $programs = $this->programRepository->getAllWithRelations();
        $stateGroups = [];

        foreach ($programs as $program) {
            $stateName = $program->programState->state ?? $program->programState->name;
            if (!isset($stateGroups[$stateName])) {
                $stateGroups[$stateName] = 0;
            }
            $stateGroups[$stateName]++;
        }

        return [
            'labels' => array_keys($stateGroups),
            'values' => array_values($stateGroups)
        ];
    }

    public function getProjectsByState(): array
    {
        $projects = Project::with('projectState')->get();
        $stateGroups = [];

        foreach ($projects as $project) {
            $stateName = $project->projectState->state ?? $project->projectState->name;
            if (!isset($stateGroups[$stateName])) {
                $stateGroups[$stateName] = 0;
            }
            $stateGroups[$stateName]++;
        }

        return [
            'labels' => array_keys($stateGroups),
            'values' => array_values($stateGroups)
        ];
    }

    public function getProjectsPerProgram(): array
    {
        $programs = $this->programRepository->getAllWithRelations();
        $labels = [];
        $values = [];

        foreach ($programs as $program) {
            $labels[] = $program->name;
            $values[] = $program->projects()->count();
        }

        return compact('labels', 'values');
    }

    public function getProjectsTimeline(): array
    {
        $projects = Project::selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $labels = [];
        $values = [];

        foreach ($projects as $item) {
            $labels[] = $item->month;
            $values[] = $item->count;
        }

        return compact('labels', 'values');
    }

    public function getProjectsProgress(): array
    {
        $ranges = [
            '0-20%' => Project::whereBetween('progress', [0, 20])->count(),
            '21-40%' => Project::whereBetween('progress', [21, 40])->count(),
            '41-60%' => Project::whereBetween('progress', [41, 60])->count(),
            '61-80%' => Project::whereBetween('progress', [61, 80])->count(),
            '81-100%' => Project::whereBetween('progress', [81, 100])->count(),
        ];

        return [
            'labels' => array_keys($ranges),
            'values' => array_values($ranges)
        ];
    }

    private function getActiveProjectsCount(): int
    {
        return Project::whereHas('projectState', function ($q) {
            $q->where('state', 'Active');
        })->count();
    }

    private function getCompletedProjectsCount(): int
    {
        return Project::whereHas('projectState', function ($q) {
            $q->where('state', 'Completed');
        })->count();
    }
}
