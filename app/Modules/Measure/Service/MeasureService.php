<?php

namespace App\Modules\Measure\Service;

use App\Modules\Measure\Domain\Measure;
use App\Modules\Measure\Repository\MeasureRepository;
use RuntimeException;

class MeasureService
{
    private MeasureRepository $measureRepository;

    public function __construct(MeasureRepository $measureRepository)
    {
        $this->measureRepository = $measureRepository;
    }

    public function createMeasure(string $name): Measure
    {
        $measure = Measure::at($name);
        $this->measureRepository->save($measure);
        return $measure;
    }

    public function getMeasureById(int $id): Measure
    {
        $measure = $this->measureRepository->findById($id);
        if (!$measure) {throw new RuntimeException("No se encontro la medida");}

        return $measure;
    }

     public function findMeasureByName(string $name): Measure
    {
        $measure = $this->measureRepository->findBy('name', trim($name));

        if (!$measure) {throw new RuntimeException("No se encontró ninguna measure con nombre: {$name}"); }
        return $measure;
    }

    public function getAllMeasures()
    {
        return $this->measureRepository->getAll();
    }

    public function updateMeasure(int $id, string $newName): Measure
    {
        $measure = $this->measureRepository->findById($id);

        if (!$measure) {throw new RuntimeException("No se encontró la medida con ID: {$id}");}

        $validatedMeasure = Measure::at($newName);
        $measure->name = $validatedMeasure->getName();

        $this->measureRepository->save($measure);

        return $measure;
    }


    public function getMeasureWithIndicators(int $id): Measure
    {
        $measure = $this->measureRepository->findWithIndicators($id);
        if (!$measure) {throw new RuntimeException("No se encontró la measure con ID: {$id}");}

        return $measure;
    }

    public function getIndicatorOfMeasureByName(int $measureId, string $indicatorName)
    {
        $measure = $this->measureRepository->findById($measureId);
        if (!$measure) {
            throw new RuntimeException("No se encontró la medida con ID: $measureId");
        }
        return $measure->findIndicatorByName($indicatorName);
    }

    public function addIndicatorToMeasure(int $measureId, $indicator): void
    {
        $measure = $this->getMeasureById($measureId);
        $measure->addIndicator($indicator);
    }

    // ESTO AUN NO ESTA IMPLEMENTADO COMO RUTA
    public function removeIndicatorFromMeasure(int $measureId, $indicator): void
    {
        $measure = $this->getMeasureById($measureId);
        $removed = $measure->removeIndicator($indicator->getName());

        if (!$removed) {
            throw new RuntimeException("El indicador especificado no existe en esta medida");
        }
    }
}