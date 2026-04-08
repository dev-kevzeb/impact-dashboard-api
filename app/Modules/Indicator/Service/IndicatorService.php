<?php

namespace App\Modules\Indicator\Service;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Indicator\Repository\IndicatorRepository;
use App\Modules\IndicatorType\Repository\IndicatorTypeRepository;
use App\Modules\Measure\Repository\MeasureRepository;
use RuntimeException;

class IndicatorService
{
    private IndicatorRepository $indicatorRepository;
    private MeasureRepository $measureRepository;
    private IndicatorTypeRepository $indicatorTypeRepository;

    public function __construct(IndicatorRepository $indicatorRepository, IndicatorTypeRepository $indicatorTypeRepository, MeasureRepository $measureRepository)
    {
        $this->indicatorRepository = $indicatorRepository;
        $this->indicatorTypeRepository = $indicatorTypeRepository;
        $this->measureRepository = $measureRepository;
    }

    public function createIndicator(string $name, float $target, int $indicatorTypeId, int $measureId, float $actualValue = 0.0): Indicator
    {
        $measure = $this->measureRepository->findById($measureId);
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        if (!$measure) throw new RuntimeException("Measure not found");
        if (!$indicatorType) throw new RuntimeException("Indicator type not found");

        $indicator = Indicator::at($name, $indicatorType, $target, $measure);
        $indicator->actual_value = $actualValue;
        $this->indicatorRepository->save($indicator);

        return $indicator;
    }

    public function getIndicatorById(int $id): Indicator
    {
        return $this->indicatorRepository->findById($id);
    }

    public function findIndicatorByName(string $name): Indicator
    {
        $indicator = $this->indicatorRepository->findByName(trim($name));
        if (!$indicator) throw new RuntimeException("The indicator does not exist");
        return $indicator;
    }

    public function getAllIndicators()
    {
        return $this->indicatorRepository->getAll();
    }

    public function updateIndicator(int $id, string $name, float $target, int $indicatorTypeId, int $measureId, float $actualValue = 0.0): Indicator
    {
        $measure = $this->measureRepository->findById($measureId);
        $indicator = $this->indicatorRepository->findById($id);
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        if (!$indicator) {
            throw new RuntimeException("Indicator with name not found: {$name}");
        }
        if (!$indicatorType) {
            throw new RuntimeException("Indicator type not found");
        }
        if (!$measure) {
            throw new RuntimeException("Measure not found");
        }

        $updatedIndicator = Indicator::at($name, $indicatorType, $target, $measure);

        $indicator->name = $updatedIndicator->name;
        $indicator->target = $updatedIndicator->target;
        $indicator->actual_value = $actualValue;
        $indicator->type_id = $updatedIndicator->type_id;
        $indicator->measure_id = $updatedIndicator->measure_id;

        $this->indicatorRepository->save($indicator);

        return $indicator;
    }

    public function getIndicatorsByMeasureId(int $id, int $perPage, ?string $search, ?array $exclude){
        return $this->indicatorRepository->getByMeasure($id, $search, $perPage, $exclude);
    }

    public function getByMeasureIds(array $measuresIds){
        return $this->indicatorRepository->getByMeasureIds($measuresIds);
    }

    public function deleteIndicator(int $id): void
    {
        $indicator = $this->indicatorRepository->findById($id);

        if ($indicator->projects()->count() > 0) {
            throw new RuntimeException('Cannot delete an indicator that is assigned to one or more projects.');
        }

        $indicator->delete();
    }
}
