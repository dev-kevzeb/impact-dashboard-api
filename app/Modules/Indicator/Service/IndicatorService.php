<?php

namespace App\Modules\Indicator\Service;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Indicator\Repository\IndicatorRepository;
use App\Modules\IndicatorType\Repository\IndicatorTypeRepository;
use RuntimeException;

class IndicatorService
{
    private IndicatorRepository $indicatorRepository;

    private IndicatorTypeRepository $indicatorTypeRepository;

    public function __construct(IndicatorRepository $indicatorRepository, IndicatorTypeRepository $indicatorTypeRepository){
        $this->indicatorRepository = $indicatorRepository;
        $this->indicatorTypeRepository = $indicatorTypeRepository;

    }

    public function createIndicator(string $name, float $target, int $indicatorTypeId): Indicator
    {
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        $indicator = Indicator::at($name, $indicatorType, $target);
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

    public function updateIndicator(int $id, string $name, float $target, int $indicatorTypeId): Indicator
    {
        $indicator = $this->indicatorRepository->findById($id);
        $indicatorType = $this->indicatorTypeRepository->findById($indicatorTypeId);

        if(!$indicator){
            throw new RuntimeException("No se encontro el indicador de nombre: {$name}");
        }
        if(!$indicatorType){
            throw new RuntimeException("No se encontro el tipo de indicador");
        }

        $updatedIndicator = Indicator::at($name, $indicatorType, $target);

        $indicator->name = $updatedIndicator->name;
        $indicator->target = $updatedIndicator->target;
        $indicator->type_id = $updatedIndicator->type_id;

        $this->indicatorRepository->save($indicator);

        return $indicator;
    }
}