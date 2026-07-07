<?php

namespace App\Modules\Statistics\Service;

use App\Modules\Agency\Repository\AgencyRepository;
use App\Modules\Country\Repository\CountryRepository;
use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use App\Modules\Donor\Repository\DonorRepository;
use App\Modules\Indicator\Repository\IndicatorRepository;
use App\Modules\Kpa\Repository\KpaRepository;
use App\Modules\Measure\Repository\MeasureRepository;
use App\Modules\Project\Repository\ProjectRepository;
use App\Modules\ProjectIndicator\Repository\ProjectIndicatorRepository;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;
use App\Modules\Statistics\Domain\BottomUp;
use App\Modules\Statistics\Domain\ContributionShare;
use App\Modules\Statistics\Domain\ImplementationAggregate;
use App\Modules\Statistics\Domain\TopDown;

class StatisticsService
{
    private ProjectIndicatorRepository $projectIndicatorRepository;
    private ProjectRepository $projectRepository;
    private KpaRepository $kpaRepository;
    private CountryKpaRepository $countryKpaRepository;
    private StrategicOutputRepository $strategicOutputRepository;
    private MeasureRepository $measureRepository;
    private IndicatorRepository $indicatorRepository;
    private AgencyRepository $agencyRepository;
    private DonorRepository $donorRepository;
    private CountryRepository $countryRepository;

    public function __construct(MeasureRepository $measureRepository, ProjectIndicatorRepository $projectIndicatorRepository, ProjectRepository $projectRepository, IndicatorRepository $indicatorRepository, StrategicOutputRepository $strategicOutputRepository, CountryKpaRepository $countryKpaRepository, KpaRepository $kpaRepository, AgencyRepository $agencyRepository, DonorRepository $donorRepository, CountryRepository $countryRepository)
    {
        $this->measureRepository = $measureRepository;
        $this->projectIndicatorRepository = $projectIndicatorRepository;
        $this->projectRepository = $projectRepository;
        $this->indicatorRepository = $indicatorRepository;
        $this->strategicOutputRepository = $strategicOutputRepository;
        $this->countryKpaRepository = $countryKpaRepository;
        $this->kpaRepository = $kpaRepository;
        $this->agencyRepository = $agencyRepository;
        $this->donorRepository = $donorRepository;
        $this->countryRepository = $countryRepository;
    }

    public function getMeasureImplementation(int $measureId):array
    {
        $measure = $this->measureRepository->findById($measureId);
        if (!$measure) throw new \RuntimeException("Measure not found");

        $indicators = $this->indicatorRepository->getWithTypeByMeasureId($measureId);
        if ($indicators->isEmpty()) return [
            "name" => $measure->name,
            "implementation" => 0,
            "total" => 1,
            "resource" => 0,
            "beneficiaries" => [],
            "agencies" => [],
            "donors" => []
        ];

        $resource = 0;
        $beneficiaries = collect();
        $agenciesRaw = collect();
        $donorsRaw = collect();
        $indicatorImplementations = [];

        foreach ($indicators as $indicator) {
            $implementationData = $this->getIndicatorImplementation($indicator->id);
            $indicatorImplementations[] = $implementationData['implementation'];

            $resource += $implementationData['resource'];
            $beneficiaries = $beneficiaries->merge($implementationData['beneficiaries']);
            $agenciesRaw = $agenciesRaw->merge($implementationData['agencies']);
            $donorsRaw = $donorsRaw->merge($implementationData['donors']);
        }

        $implementation = 0;
        if (!empty($indicatorImplementations)) 
            $implementation = min(round(array_sum($indicatorImplementations), 2), 100);

        $beneficiaries = $beneficiaries->unique('id')->values();

        $agenciesContribution = (new ContributionShare($agenciesRaw->all(), $implementation))->value();
        $donorsContribution = (new ContributionShare($donorsRaw->all(), $implementation))->value();

        return [
            "name" => $measure->name,
            "implementation" => $implementation,
            "total" => 1,
            "resource" => $resource,
            "beneficiaries" => $beneficiaries,
            "agencies" => $agenciesContribution,
            "donors" => $donorsContribution
        ];
    }

    public function getIndicatorImplementation(int $indicatorId): array
    {
        $indicator = $this->indicatorRepository->findById($indicatorId);
        if (!$indicator) return [
            'name' => 'indicator',
            'implementation' => 0,
            'resource' => 0,
            'beneficiaries' => [],
            'agencies' => [],
            'donors' => [],
            'project_ids' => []
        ];

        if (!isset($indicator->type)) $indicator->load('type');
        

        $isBottomUp = (bool) ($indicator->type?->is_bottom_up ?? true);

        if (!$isBottomUp) {
            $implementation = $this->calculateIndicatorImplementation($indicator);

            return [
                'name' => $indicator->name,
                'implementation' => $implementation,
                'resource' => 0,
                'beneficiaries' => [],
                'agencies' => [],
                'donors' => [],
                'project_ids' => []
            ];
        }

        $projectIds = $this->projectIndicatorRepository->getProjectIdsByIndicatorIds([$indicatorId]);
        if (empty($projectIds)) {
            return [
                'name' => $indicator->name,
                'implementation' => 0,
                'resource' => 0,
                'beneficiaries' => [],
                'agencies' => [],
                'donors' => [],
                'project_ids' => []
            ];
        }

        $projects = $this->projectRepository->getByIds($projectIds);

        $resource = 0;
        foreach ($projects as $project) {
            $resource += $project->project_budget;
        }

        $implementation = $this->calculateIndicatorImplementation($indicator);

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

        $beneficiaries = $projects->map(fn($project) => $project->beneficiary)->filter()->unique('id')->values();

        return [
            'name' => $indicator->name,
            'implementation' => $implementation,
            'resource' => $resource,
            'beneficiaries' => $beneficiaries,
            'agencies' => $allAgenciesContributions,
            'donors' => $allDonorsContributions,
            'project_ids' => $projectIds
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

        $nodes = $measures->map(fn($measure) => $this->getMeasureImplementation($measure->id));
        $aggregate = (new ImplementationAggregate($nodes))->value();

        return [
            "name" => "{$aggregate['total']} measures",
            ...$aggregate,
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

        $nodes = $strategicOutputs->map(fn($so) => $this->getStrategicOutputImplementation($so->id));
        $aggregate = (new ImplementationAggregate($nodes))->value();

        return [
            "name" => "{$aggregate['total']} measures",
            ...$aggregate,
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

        $nodes = collect();
        $kpaBeneficiaries = [];

        foreach ($kpas as $kpa) {
            $implementation = $this->getKpaImplementation($kpa->id);
            $nodes->push($implementation);

            $kpaBeneficiaries[] = [
                "name" => $kpa->name,
                "beneficiaries" => $implementation['beneficiaries']
            ];
        }

        $aggregate = (new ImplementationAggregate($nodes))->value();

        return [
            "name" => "{$aggregate['total']} measures ",
            "implementation" => $aggregate['implementation'],
            "resource" => $aggregate['resource'],
            "beneficiaries" => $kpaBeneficiaries,
            "agencies" => $this->appendMissingContributors($aggregate['agencies'], $this->agencyRepository->getAll()),
            "donors" => $this->appendMissingContributors($aggregate['donors'], $this->donorRepository->getAll())
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

    public function getCountryDashboardImplementation(int $countryId, ?string $search, int $perPage): array
    {
        $country = $this->countryRepository->findById($countryId);
        $paginator = $this->countryKpaRepository->getCountryKpasByCountryId($countryId, $search, $perPage);

        $kpas = [];
        foreach ($paginator as $countryKpa) {
            $kpas[] = $this->buildCountryKpaImplementationNode($countryKpa);
        }

        return [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'currency_id' => $country->currency_id,
                'active' => (bool) $country->active,
            ],
            'kpas' => $kpas,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function buildCountryKpaImplementationNode(object $countryKpa): array
    {
        $strategicOutputs = $this->strategicOutputRepository->getByCountryKpaIds([$countryKpa->id]);
        $strategicOutputNodes = [];
        $totalMeasures = 0;
        $totalIndicators = 0;

        foreach ($strategicOutputs as $strategicOutput) {
            $measures = $this->measureRepository->getAllByStrategicOutputId($strategicOutput->id);
            $measureNodes = [];
            $strategicOutputImplementation = 0.0;
            $strategicOutputIndicatorCount = 0;

            foreach ($measures as $measure) {
                $indicators = $this->indicatorRepository->getWithTypeByMeasureId($measure->id);
                $indicatorNodes = [];
                $measureImplementation = 0.0;

                foreach ($indicators as $indicator) {
                    $indicatorNode = $this->buildIndicatorImplementationNode($indicator);
                    $measureImplementation += $indicatorNode['implementation'];
                    $indicatorNodes[] = $indicatorNode;
                }

                $measureImplementation = min(round($measureImplementation, 2), 100);
                $strategicOutputImplementation += $measureImplementation;
                $strategicOutputIndicatorCount += count($indicatorNodes);

                $measureNodes[] = [
                    'id' => $measure->id,
                    'name' => $measure->name,
                    'implementation' => $measureImplementation,
                    'indicators_count' => count($indicatorNodes),
                    'indicators' => $indicatorNodes,
                ];
            }

            $measureCount = count($measureNodes);
            $strategicOutputImplementation = $measureCount > 0 ? round($strategicOutputImplementation / $measureCount, 2) : 0;
            $totalMeasures += $measureCount;
            $totalIndicators += $strategicOutputIndicatorCount;

            $strategicOutputNodes[] = [
                'id' => $strategicOutput->id,
                'name' => $strategicOutput->name,
                'implementation' => $strategicOutputImplementation,
                'measures_count' => $measureCount,
                'indicators_count' => $strategicOutputIndicatorCount,
                'measures' => $measureNodes,
            ];
        }

        $weightedTotal = 0.0;
        foreach ($strategicOutputNodes as $strategicOutputNode) {
            $weightedTotal += $strategicOutputNode['implementation'] * $strategicOutputNode['measures_count'];
        }

        return [
            'id' => $countryKpa->id,
            'id_kpa' => $countryKpa->id_kpa,
            'name' => data_get($countryKpa, 'kpa.name', ''),
            'implementation' => $totalMeasures > 0 ? round($weightedTotal / $totalMeasures, 2) : 0,
            'strategic_outputs_count' => (int) ($countryKpa->strategic_outputs_count ?? count($strategicOutputNodes)),
            'measures_count' => (int) ($countryKpa->measures_count ?? $totalMeasures),
            'indicators_count' => (int) ($countryKpa->indicators_count ?? $totalIndicators),
            'strategic_outputs' => $strategicOutputNodes,
        ];
    }

    private function buildIndicatorImplementationNode(object $indicator): array
    {
        $implementation = $this->calculateIndicatorImplementation($indicator);

        return [
            'id' => $indicator->id,
            'name' => $indicator->name,
            'target' => $indicator->target,
            'actual_value' => $indicator->actual_value,
            'implementation' => $implementation,
            'type' => [
                'id' => $indicator->type?->id,
                'name' => $indicator->type?->name,
                'is_bottom_up' => (bool) ($indicator->type?->is_bottom_up ?? true),
            ],
        ];
    }

    private function calculateIndicatorImplementation(object $indicator): float
    {
        if (!isset($indicator->type)) $indicator->load('type');        

        $isBottomUp = (bool) ($indicator->type?->is_bottom_up ?? true);

        if ($isBottomUp) {
            $projectIds = $this->projectIndicatorRepository->getProjectIdsByIndicatorIds([$indicator->id]);

            if (empty($projectIds)) return 0.0;

            $projects = $this->projectRepository->getByIds($projectIds);
            $bottomUpProjects = $projects->map(function ($project) {
                return [
                    'implementation' => max(0.0, min(100.0, (float) ($project->progress ?? 0))) / 100,
                    'weight' => max(0.0, min(1.0, (float) ($project->weight ?? 0))),
                ];
            })->toArray();

            if (empty($bottomUpProjects)) return 0.0;
            

            try {
                return min((new BottomUp($bottomUpProjects))->value(), 100);
            } catch (\RuntimeException $e) {
                return 0.0;
            }
        }

        $target = (float) ($indicator->target ?? 0);
        $actualValue = (float) ($indicator->actual_value ?? 0);

        if ($target <= 0 || $actualValue < 0) return 0.0;
        

        try {
            return min((new TopDown($actualValue, $target))->value(), 100);
        } catch (\RuntimeException $e) {
            return 0.0;
        }
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

        $nodes = $strategicOutputs->map(fn($so) => $this->getStrategicOutputImplementation($so->id));
        $aggregate = (new ImplementationAggregate($nodes))->value();

        return [
            "name" => "{$aggregate['total']} measures",
            ...$aggregate,
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

        $nodes = collect();
        $kpaBeneficiaries = [];

        foreach ($countryKpas as $countryKpa) {
            $implementation = $this->getCountryKpaImplementation($countryId, $countryKpa->id_kpa);
            $nodes->push($implementation);

            $kpaBeneficiaries[] = [
                "name" => $countryKpa->kpa->name,
                "beneficiaries" => $implementation['beneficiaries']
            ];
        }

        $aggregate = (new ImplementationAggregate($nodes))->value();

        return [
            "name" => "{$aggregate['total']} measures ",
            "implementation" => $aggregate['implementation'],
            "resource" => $aggregate['resource'],
            "beneficiaries" => $kpaBeneficiaries,
            "agencies" => $this->appendMissingContributors($aggregate['agencies'], $this->agencyRepository->getAll()),
            "donors" => $this->appendMissingContributors($aggregate['donors'], $this->donorRepository->getAll())
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
            "kpas" => $kpasData->values()->toArray(),
            "resource" => $resource
        ];
    }

    private function appendMissingContributors(array $contributions, $registeredEntities): array
    {
        $existingById = collect($contributions)->keyBy('id');

        foreach ($registeredEntities as $entity) {
            if ($existingById->has($entity->id)) continue;

            $existingById->put($entity->id, [
                'id' => $entity->id,
                'name' => $entity->name,
                'contribution' => 0,
            ]);
        }

        return $existingById->values()->toArray();
    }

}