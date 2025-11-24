<?php

namespace App\Modules\Measure\Service;

use App\Modules\Measure\Domain\Measure;
use App\Modules\Measure\Repository\MeasureRepository;
use App\Modules\StrategicOutput\Domain\StrategicOutput;

use RuntimeException;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;

class MeasureService
{
    private MeasureRepository $measureRepository;
    private StrategicOutputRepository $strategicOutputRepository;

    public function __construct(MeasureRepository $measureRepository, StrategicOutputRepository $strategicOutputRepository)
    {
        $this->measureRepository = $measureRepository;
        $this->strategicOutputRepository = $strategicOutputRepository;
    }

    public function createMeasure(string $name, int $strategicOutputId): Measure
    {
        $strategicOutput = $this->strategicOutputRepository->findById($strategicOutputId);
        if (!$strategicOutput instanceof StrategicOutput) throw new RuntimeException("El resultado estratégico con id {$strategicOutputId} no existe.");
        
        $measure = Measure::at($name, $strategicOutput);

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

    public function updateMeasure(int $id, string $newName, int $strategicOutputId): Measure
    {
        $measure = $this->measureRepository->findById($id);
        $strategicOutput = $this->strategicOutputRepository->findById($strategicOutputId);

        if (!$measure) {throw new RuntimeException("No se encontró la medida con ID: {$id}");}
        if (!$strategicOutput instanceof StrategicOutput) throw new RuntimeException("El resultado estratégico con id {$strategicOutputId} no existe.");

        $validatedMeasure = Measure::at($newName, $strategicOutput);
        $measure->name = $validatedMeasure->getName();
        $measure->strategic_output_id = $validatedMeasure->strategic_output_id;

        $this->measureRepository->save($measure);

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
        if (!$measure) throw new \RuntimeException("La medida con id {$measure} no existe.");

        $measure->addIndicator($indicator);
    }

    public function removeIndicatorFromMeasure(int $measureId, $indicator): void
    {
        $measure = $this->getMeasureById($measureId);
        $removed = $measure->removeIndicator($indicator->getName());

        if (!$removed) {
            throw new RuntimeException("El indicador especificado no existe en esta medida");
        }
    }
}