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

    public function __construct(IndicatorRepository $indicatorRepository, IndicatorTypeRepository $indicatorTypeRepository, MeasureRepository $measureRepository){
        $this->indicatorRepository = $indicatorRepository;
        $this->indicatorTypeRepository = $indicatorTypeRepository;
        $this->measureRepository = $measureRepository;

    }

    public function createIndicator(string $name, float $target, int $indicatorTypeId, int $measureId): Indicator
    {
        $measure = $this->measureRepository->findById($measureId);
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        if (!$measure) {
            throw new RuntimeException("Medida no encontrada");
        } if (!$indicatorType) {
            throw new RuntimeException("Tipo de indicador no encontrado");
        }

        $indicator = Indicator::at($name, $indicatorType, $target, $measure);
        $this->indicatorRepository->save($indicator);

        return $indicator;
    }

    public function getIndicatorById(int $id): Indicator
    {
        return $this->indicatorRepository->findById($id);
    }

    public function findIndicatorByName(string $name): Indicator
    {
        return $this->indicatorRepository->findBy('name',trim($name));
    }

    public function getAllIndicators()
    {
        return $this->indicatorRepository->getAll();
    }

    public function updateIndicator(int $id, string $name, float $target, int $indicatorTypeId, int $measureId): Indicator
    {
        $measure = $this->measureRepository->findById($measureId);
        $indicator = $this->indicatorRepository->findById($id);
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        if(!$indicator){
            throw new RuntimeException("No se encontro el indicador de nombre: {$name}");
        } if(!$indicatorType){
            throw new RuntimeException("No se encontro el tipo de indicador");
        } if(!$measure){
            throw new RuntimeException("No se encontro la medida");
        }

        $updatedIndicator = Indicator::at($name, $indicatorType, $target, $measure);

        $indicator->name = $updatedIndicator->name;
        $indicator->target = $updatedIndicator->target;
        $indicator->type_id = $updatedIndicator->type_id;
        $indicator->measure_id = $updatedIndicator->measure_id;

        $this->indicatorRepository->save($indicator);

        return $indicator;
    }
}