<?php

namespace App\Modules\Statistics\Service;

use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use App\Modules\Indicator\Repository\IndicatorRepository;
use App\Modules\Kpa\Repository\KpaRepository;
use App\Modules\Measure\Repository\MeasureRepository;
use App\Modules\Statistics\Domain\BottomUp;
use App\Modules\Statistics\Domain\TopDown;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectIndicator\Repository\ProjectIndicatorRepository;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;
use Dom\Implementation;
use Log;

class StatisticsService
{
    private ProjectIndicatorRepository $projectIndicatorRepository;
    private ProjectRepository $projectRepository;
    private KpaRepository $kpaRepository;
    private CountryKpaRepository $countryKpaRepository;
    private StrategicOutputRepository $strategicOutputRepository;
    private MeasureRepository $measureRepository;
    private IndicatorRepository $indicatorRepository;

    public function __construct(MeasureRepository $measureRepository, ProjectIndicatorRepository $projectIndicatorRepository, ProjectRepository $projectRepository, IndicatorRepository $indicatorRepository, StrategicOutputRepository $strategicOutputRepository, CountryKpaRepository $countryKpaRepository, KpaRepository $kpaRepository)
    {
        $this->measureRepository = $measureRepository;
        $this->projectIndicatorRepository = $projectIndicatorRepository;
        $this->projectRepository = $projectRepository;
        $this->indicatorRepository = $indicatorRepository;
        $this->strategicOutputRepository = $strategicOutputRepository;
        $this->countryKpaRepository = $countryKpaRepository;
        $this->kpaRepository = $kpaRepository;
    }

    public function getMeasureImplementation(int $measureId):array
    {
        $measure = $this->measureRepository->findById($measureId);
        if (!$measure) throw new \RuntimeException("Measure not found");

        $indicators = $this->indicatorRepository->getIdsByMeasure($measureId);
        if(empty($indicators)) return [
            "name" => $measure->name,
            "implementation" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $projectIds = $this->projectIndicatorRepository->getProjectIdsByIndicatorIds($indicators);
        if(empty($projectIds)) {
            $targets = $this->indicatorRepository->getTargetsByMeasureId($measureId);
            $target = array_sum($targets);
            
            return [
                "name" => $measure->name,
                "implementation" => TopDown::calculate($target/5, $target)->value(),
                "resource" => 0,
                "beneficiaries" => [],
                "agencies" => [],
                "donors" => []
            ];
        }

        $projects = $this->projectRepository->getByIds($projectIds);

        $chartData = [];
        $total = $projects->count();
        $resource = 0;

        $agenciesContribution = [];

        foreach ($projects as $project) {
            $chartData[] = [
                'implementation' => $project->progress/100,
                'weight' => 1/$total,
            ];
            $resource += $project->project_budget;
        }

        $implementation = BottomUp::calculate($chartData)->value();

        $allDonorsContributions = $projects->flatMap(function ($project) use ($total) {return $this->calculateDonorsContribution($project->donors, $project->progress/100, 1/$total);});
        $allAgenciesContributions = $projects->flatMap(function ($project) use ($total) {return $this->calculateAgenciesContribution($project->agencies, $project->progress/100, 1/$total);});

        $donorsContribution = $allDonorsContributions->groupBy('id')->map(function ($group) use ($implementation) {
            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $implementation > 0 ? round($group->sum('contribution') / $implementation * 100, 2) : 0
            ];
        })->values()->toArray();
        
        $agenciesContribution = $allAgenciesContributions->groupBy('id')->map(function ($group)use ($implementation) {
            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $implementation > 0 ? round($group->sum('contribution') / $implementation * 100, 2) : 0
            ];
        })->values()->toArray();

        $beneficiaries = $projects->map(fn($project) => $project->beneficiary)->filter()->unique('id')->values();

         return [
            "name" => $measure->name,
            "implementation" => $implementation,
            "resource" => $resource,
            "beneficiaries" => $beneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getStrategicOutputImplementation(int $strategicOutputId): array
    {
        $measures = $this->measureRepository->getAllByStrategicOutputId($strategicOutputId);

        if ($measures->isEmpty()) return [
                "name" => "0 measures",
                "total" => 0,
                "implementation" => 0,
                "resource" => 0,
                "beneficiaries" => [],
                "agencies" => [],
                "donors" => []
            ];
            
        $total = 0;
        $resource = 0;

        $beneficiaries = collect();
        $agenciesRaw = collect();
        $donorsRaw = collect();
        
        foreach ($measures as $measure) {
            $implementation = $this->getMeasureImplementation($measure->id);
            $total += $implementation['implementation'];
            $resource += $implementation['resource'];

            $beneficiaries = $beneficiaries->merge($implementation['beneficiaries']);

            $agenciesRaw = $agenciesRaw->merge($implementation['agencies']);
            $donorsRaw = $donorsRaw->merge($implementation['donors']);
        }

        $total = $total / $measures->count();

        $beneficiaries = $beneficiaries->unique('id')->values();

        $totalCombinedContribution = $agenciesRaw->sum('contribution') + $donorsRaw->sum('contribution');

        $agenciesContribution = $agenciesRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {

            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();

        $donorsContribution = $donorsRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {

            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();
        
        return [
            "name" => "{$measures->count()} measures",
            "total" => $measures->count(),
            "implementation" => $total,
            "resource" => $resource,
            "beneficiaries" => $beneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getKpaImplementation(int $kpaId): array
    {
        $countryKpas = $this->countryKpaRepository->getIdsByKpaId($kpaId);
        $strategicOutputs = $this->strategicOutputRepository->getByCountryKpaIds($countryKpas->toArray());

        if ($strategicOutputs->isEmpty()) return [
            "name" => "0 measures",
            "implementation" => 0,
            "total" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $total = 0;
        $measures = 0;
        $resource = 0;
        $beneficiaries = collect();
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($strategicOutputs as $so) {
            $implementation = $this->getStrategicOutputImplementation($so->id);
            $total += $implementation['implementation'];
            $measures += $implementation['total'];
            $resource += $implementation['resource'];
            $beneficiaries = $beneficiaries->merge($implementation['beneficiaries']);
            $agenciesRaw = $agenciesRaw->merge($implementation['agencies']);
            $donorsRaw = $donorsRaw->merge($implementation['donors']);
        }

        $beneficiaries = $beneficiaries->unique('id')->values();
        $totalCombinedContribution = $agenciesRaw->sum('contribution') + $donorsRaw->sum('contribution');

        $agenciesContribution = $agenciesRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {
            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();

        $donorsContribution = $donorsRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {
            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();
        
        return [
            "name" => "{$measures} measures",
            "implementation" => $total / $strategicOutputs->count(),
            "total" => $measures,
            "resource" => $resource,
            "beneficiaries" => $beneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getOverallImplementation(): array
    {
        $kpas = $this->kpaRepository->getAll();

        if ($kpas->isEmpty()) return [
            "name" => "0 measures",
            "implementation" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $total = 0;
        $measures = 0;
        $resource = 0;

        $kpaBeneficiaries = [];
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($kpas as $kpa) {
            $implementation = $this->getKpaImplementation($kpa->id);
            $total += $implementation['implementation'];
            $measures += $implementation['total'];
            $resource += $implementation['resource'];
            
            $kpaBeneficiaries[] = [
                "name" => $kpa->name,
                "beneficiaries" => $implementation['beneficiaries']
            ];
            $agenciesRaw = $agenciesRaw->merge($implementation['agencies']);
            $donorsRaw = $donorsRaw->merge($implementation['donors']);
        }

        $totalCombinedContribution = $agenciesRaw->sum('contribution') + $donorsRaw->sum('contribution');

        $agenciesContribution = $agenciesRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {
            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();

        $donorsContribution = $donorsRaw->groupBy('id')->map(function ($group) use ($totalCombinedContribution) {
            $sum = $group->sum('contribution');

            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $totalCombinedContribution > 0 ? round(($sum / $totalCombinedContribution) * 100, 2) : 0
            ];
        })->values()->toArray();

        return [
            "name" => "{$measures} measures ",
            "implementation" => $total / $kpas->count(),
            "resource" => $resource,
            "beneficiaries" => $kpaBeneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function calculateAgenciesContribution($agencies, $progress, $weight):array{
        $donations = [];
        foreach($agencies as $agency) {
            $donations[] = [
                'id' => $agency->id,
                'name' => $agency->name,
                'contribution' => number_format($progress * ($agency->pivot->contribution / 100) * $weight * 100,2,'.','')
            ];
        }
        return $donations;
    }

    public function calculateDonorsContribution($donors, $progress, $weight): array {
        $donations = [];
        foreach($donors as $donor) {
            $donations[] = [
                'id' => $donor->id,
                'name' => $donor->name,
                'contribution' => number_format($progress * ($donor->pivot->contribution / 100) * $weight * 100,2,'.','')
            ];
        }
        return $donations;
    }
}