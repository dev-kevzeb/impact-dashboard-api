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

        $indicators = $this->indicatorRepository->getWithTypeByMeasureId($measureId);
        if ($indicators->isEmpty()) return [
            "name" => $measure->name,
            "implementation" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $isBottomUp = (bool) ($indicators->first()->type->is_bottom_up ?? true);

        if (!$isBottomUp) {
            $tdTotal = 0;
            $tdCount = 0;
            foreach ($indicators as $indicator) {
                if ($indicator->target > 0) {
                    $tdTotal += (new TopDown((float) ($indicator->actual_value ?? 0.0), (float) $indicator->target))->value();
                    $tdCount++;
                }
            }
            $implementation = $tdCount > 0 ? round($tdTotal / $tdCount, 2) : 0;

            return [
                "name" => $measure->name,
                "implementation" => $implementation,
                "resource" => 0,
                "beneficiaries" => [],
                "agencies" => [],
                "donors" => []
            ];
        }

        $indicatorIds = $indicators->pluck('id')->toArray();
        $projectIds = $this->projectIndicatorRepository->getProjectIdsByIndicatorIds($indicatorIds);
        if (empty($projectIds)) {
            return [
                "name" => $measure->name,
                "implementation" => 0,
                "resource" => 0,
                "beneficiaries" => [],
                "agencies" => [],
                "donors" => []
            ];
        }

        $projects = $this->projectRepository->getByIds($projectIds);

        $chartData = [];
        $resource = 0;

        foreach ($projects as $project) {
            $chartData[] = [
                'implementation' => $project->progress / 100,
                'weight' => (float) ($project->weight ?? 0),
            ];
            $resource += $project->project_budget;
        }

        $implementation = (new BottomUp($chartData))->value();

        $allDonorsContributions = $projects->flatMap(function ($project) {
            return $this->calculateDonorsContribution(
                $project->donors,
                $project->progress / 100,
                (float) ($project->weight ?? 0)
            );
        });
        $allAgenciesContributions = $projects->flatMap(function ($project) {
            return $this->calculateAgenciesContribution(
                $project->agencies,
                $project->progress / 100,
                (float) ($project->weight ?? 0)
            );
        });

        $donorsContribution = $allDonorsContributions->groupBy('id')->map(function ($group) use ($implementation) {
            return [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'contribution' => $implementation > 0 ? round($group->sum('contribution') / $implementation * 100, 2) : 0
            ];
        })->values()->toArray();

        $agenciesContribution = $allAgenciesContributions->groupBy('id')->map(function ($group) use ($implementation) {
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

        $weightedTotal = 0;
        $measures = 0;
        $resource = 0;
        $beneficiaries = collect();
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($strategicOutputs as $so) {
            $implementation = $this->getStrategicOutputImplementation($so->id);
            $soMeasureCount = $implementation['total'];
            $weightedTotal += $implementation['implementation'] * $soMeasureCount;
            $measures += $soMeasureCount;
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
            "implementation" => $measures > 0 ? round($weightedTotal / $measures, 2) : 0,
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

        $weightedTotal = 0;
        $measures = 0;
        $resource = 0;

        $kpaBeneficiaries = [];
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($kpas as $kpa) {
            $implementation = $this->getKpaImplementation($kpa->id);
            $kpaMeasureCount = $implementation['total'];
            $weightedTotal += $implementation['implementation'] * $kpaMeasureCount;
            $measures += $kpaMeasureCount;
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
            "implementation" => $measures > 0 ? round($weightedTotal / $measures, 2) : 0,
            "resource" => $resource,
            "beneficiaries" => $kpaBeneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getAllKpasImplementation(): array
    {
        $kpas = $this->kpaRepository->getAll();

        if ($kpas->isEmpty()) return [
            "kpas" => [],
            "resource" => [],
        ];
        $resource = 0;
        $kpasData = collect();

        foreach ($kpas as $kpa) {
            $implementation = $this->getKpaImplementation($kpa->id);
            unset($implementation['name']);
            $kpasData->push([
                "id" => $kpa->id,
                "name" => $kpa->name,
                ...$implementation
            ]);
            $resource += $implementation['resource'];
        }

        return [
            "kpas"=> $kpasData->values()->toArray(),
            "resource" => $resource
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

    public function getCountryKpaImplementation(int $countryId, int $kpaId): array
    {
        $countryKpa = $this->countryKpaRepository->getByCountryAndKpa($countryId, $kpaId)->first();
        if (!$countryKpa) return [
            "name" => "0 measures",
            "implementation" => 0,
            "total" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $strategicOutputs = $this->strategicOutputRepository->getByCountryKpaIds([$countryKpa->id]);

        if ($strategicOutputs->isEmpty()) return [
            "name" => "0 measures",
            "implementation" => 0,
            "total" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $weightedTotal = 0;
        $measures = 0;
        $resource = 0;
        $beneficiaries = collect();
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($strategicOutputs as $so) {
            $implementation = $this->getStrategicOutputImplementation($so->id);
            $soMeasureCount = $implementation['total'];
            $weightedTotal += $implementation['implementation'] * $soMeasureCount;
            $measures += $soMeasureCount;
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
            "implementation" => $measures > 0 ? round($weightedTotal / $measures, 2) : 0,
            "total" => $measures,
            "resource" => $resource,
            "beneficiaries" => $beneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getCountryOverallImplementation(int $countryId): array
    {
        $countryKpas = $this->countryKpaRepository->getByCountry($countryId);

        if ($countryKpas->isEmpty()) return [
            "name" => "0 measures",
            "implementation" => 0,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $weightedTotal = 0;
        $measures = 0;
        $resource = 0;

        $kpaBeneficiaries = [];
        $agenciesRaw = collect();
        $donorsRaw = collect();

        foreach ($countryKpas as $countryKpa) {
            $implementation = $this->getCountryKpaImplementation($countryId, $countryKpa->id_kpa);
            $kpaMeasureCount = $implementation['total'];
            $weightedTotal += $implementation['implementation'] * $kpaMeasureCount;
            $measures += $kpaMeasureCount;
            $resource += $implementation['resource'];
            
            $kpaBeneficiaries[] = [
                "name" => $countryKpa->kpa->name,
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
            "implementation" => $measures > 0 ? round($weightedTotal / $measures, 2) : 0,
            "resource" => $resource,
            "beneficiaries" => $kpaBeneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getCountryAllKpasImplementation(int $countryId): array
    {
        $countryKpas = $this->countryKpaRepository->getByCountry($countryId);

        if ($countryKpas->isEmpty()) return [
            "kpas" => [],
            "resource" => [],
        ];
        
        $resource = 0;
        $kpasData = collect();

        foreach ($countryKpas as $countryKpa) {
            $implementation = $this->getCountryKpaImplementation($countryId, $countryKpa->id_kpa);
            unset($implementation['name']);
            $kpasData->push([
                "id" => $countryKpa->id_kpa,
                "name" => $countryKpa->kpa->name,
                ...$implementation
            ]);
            $resource += $implementation['resource'];
        }

        return [
            "kpas"=> $kpasData->values()->toArray(),
            "resource" => $resource
        ];
    }

}