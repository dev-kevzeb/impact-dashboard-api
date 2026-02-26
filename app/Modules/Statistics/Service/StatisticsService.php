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
        ];

        $projectIds = $this->projectIndicatorRepository->getProjectIdsByIndicatorIds($indicators);
        if(empty($projectIds)) {
            $targets = $this->indicatorRepository->getTargetsByMeasureId($measureId);
            $target = array_sum($targets);
            
            return [
                "name" => $measure->name,
                "implementation" => TopDown::calculate($target/5, $target)->value(),
            ];
        }

        $projects = $this->projectRepository->getByIds($projectIds);

        $chartData = [];
        $total = $projects->count();

        foreach ($projects as $project) {
            $chartData[] = [
                'implementation' => $project->progress,
                'weight' => 1/$total,
            ];
        }
        return [
            "name" => $measure->name,
            "implementation" => BottomUp::calculate($chartData)->value()
        ];
    }

    public function getStrategicOutputImplementation(int $strategicOutputId): array
    {
        $measures = $this->measureRepository->getAllByStrategicOutputId($strategicOutputId);

        if ($measures->isEmpty()) return [
                "name" => "0 measures",
                "total" => 0,
                "implementation" => 0,
            ];
        $total = 0;
        foreach ($measures as $measure) $total += $this->getMeasureImplementation($measure->id)['implementation'];
        
        return [
            "name" => "{$measures->count()} measures",
            "total" => $measures->count(),
            "implementation" => $total / $measures->count(),
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
        ];

        $total = 0;
        $measures = 0;

        foreach ($strategicOutputs as $so) {
            $implementation = $this->getStrategicOutputImplementation($so->id);
            $total += $implementation['implementation'];
            $measures += $implementation['total'];
        }
        
        return [
            "name" => "{$measures} measures",
            "implementation" => $total / $strategicOutputs->count(),
            "total" => $measures,
        ];
    }

    public function getOverallImplementation(): array
    {
        $kpas = $this->kpaRepository->getAll();

        if ($kpas->isEmpty()) return [
            "name" => "0 measures",
            "implementation" => 0,
        ];

        $total = 0;
        $measures = 0;
        foreach ($kpas as $kpa) {
            $implementation = $this->getKpaImplementation($kpa->id);
            $total += $implementation['implementation'];
            $measures += $implementation['total'];
        }

        return [
            "name" => "{$measures} measures ",
            "implementation" => $total / $kpas->count(),
        ];
    }
}