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
    public function createIndicatorType(string $name, bool $isBottomUp = true): IndicatorType
    {
        if($this->indicatorTypeRepository->existsByName(trim($name))) throw new RuntimeException("There is already a type of indicator with that name");

        $indicatorType = IndicatorType::at($name, $isBottomUp);
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

    public function updateIndicatorType(int $id, string $name, bool $isBottomUp = true): IndicatorType
    {
        $indicatorType = $this->indicatorTypeRepository->findById($id);
        $updateIndicatorType = IndicatorType::at($name, $isBottomUp);
        $indicatorType->name = $updateIndicatorType->name;
        $indicatorType->is_bottom_up = $updateIndicatorType->is_bottom_up;

        $this->indicatorTypeRepository->save($indicatorType);

        return $indicatorType;
    }

    public function getPaginatedIndicatorTypes(int $perPage = 10, ?string $search)
    {
        return $this->indicatorTypeRepository->getPaginated($perPage, $search);
    }

    public function deleteIndicatorType(int $id): void
    {
        $indicatorType = $this->indicatorTypeRepository->findById($id);
        if ($indicatorType->indicators()->count() > 0) {
            throw new RuntimeException('Cannot delete an indicator type that is assigned to one or more indicators.');
        }
        $indicatorType->delete();
    }
}