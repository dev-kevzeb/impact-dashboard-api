<?php

namespace App\Modules\IndicatorType\Service;

use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\IndicatorType\Repository\IndicatorTypeRepository;
use RuntimeException;

class IndicatorTypeService
{
    private IndicatorTypeRepository $indicatorTypeRepository;

    public function __construct(IndicatorTypeRepository $indicatorTypeRepository)
    {
        $this->indicatorTypeRepository = $indicatorTypeRepository;
    }
    public function createIndicatorType(string $name): IndicatorType
    {
        if($this->indicatorTypeRepository->existsByName(trim($name))) throw new RuntimeException("There is already a type of indicator with that name");

        $indicatorType = IndicatorType::at($name);
        $this->indicatorTypeRepository->save( $indicatorType);
        return $indicatorType;
    }

    public function getIndicatorTypeById(int $id): IndicatorType
    {
        return $this->indicatorTypeRepository->findById($id);
    }

    public function findIndicatorTypeByName(string $name): IndicatorType
    {
        return $this->indicatorTypeRepository->findBy('name',$name);
    }

    public function getAllIndicatorTypes()
    {
        return $this->indicatorTypeRepository->getAll();
    }

    public function updateIndicatorType(int $id, string $name): IndicatorType
    {
        $indicatorType = $this->indicatorTypeRepository->findById($id);
        $updateIndicatorType = IndicatorType::at($name);
        $indicatorType->name = $updateIndicatorType->name;

        $this->indicatorTypeRepository->save($indicatorType);

        return $indicatorType;
    }
}