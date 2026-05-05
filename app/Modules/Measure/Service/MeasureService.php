<?php

namespace App\Modules\Measure\Service;

use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Measure\Repository\MeasureRepository;
use App\Modules\StrategicOutput\Domain\StrategicOutput;

use RuntimeException;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;

class MeasureService
{
    private MeasureRepository $measureRepository;
    private StrategicOutputRepository $strategicOutputRepository;
    private CountryKpaRepository $countryKpaRepository;

    public function __construct(MeasureRepository $measureRepository, StrategicOutputRepository $strategicOutputRepository, CountryKpaRepository $countryKpaRepository)
    {
        $this->measureRepository = $measureRepository;
        $this->strategicOutputRepository = $strategicOutputRepository;
        $this->countryKpaRepository = $countryKpaRepository;
    }

    private function assertCountryNotActiveByStrategicOutput(StrategicOutput $strategicOutput): void
    {
        $countryKpa = $this->countryKpaRepository->getById($strategicOutput->id_ck);
        if ($countryKpa->country->active) {
            throw new RuntimeException('Cannot add or remove measures while the country is active.');
        }
    }

    public function createMeasure(string $name, int $strategicOutputId): Measure
    {
        $strategicOutput = $this->strategicOutputRepository->findById($strategicOutputId);
        if (!$strategicOutput instanceof StrategicOutput) throw new RuntimeException("The strategic output with id {$strategicOutputId} does not exist.");

        $this->assertCountryNotActiveByStrategicOutput($strategicOutput);

        $measure = Measure::at($name, $strategicOutput);

        $this->measureRepository->save($measure);
        return $measure;
    }

    public function getMeasureById(int $id): Measure
    {
        $measure = $this->measureRepository->findById($id);
        if (!$measure) {
            throw new RuntimeException("Measure not found");
        }

        return $measure;
    }

    public function findMeasureByName(string $name): Measure
    {
        $measure = $this->measureRepository->findByName(trim($name));

        if (!$measure) {
            throw new RuntimeException("No measure found with name: {$name}");
        }
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

        if (!$measure) {
            throw new RuntimeException("Measure with ID: {$id} not found");
        }
        if (!$strategicOutput instanceof StrategicOutput) throw new RuntimeException("The strategic output with id {$strategicOutputId} does not exist.");

        $validatedMeasure = Measure::at($newName, $strategicOutput);
        $measure->name = $validatedMeasure->getName();
        $measure->strategic_output_id = $validatedMeasure->strategic_output_id;

        $this->measureRepository->save($measure);

        return $measure;
    }

    public function getMeasuresByStrategicOutputId(int $id, int $perPage, ?string $search)
    {
        return $this->measureRepository->getByStrategicOutput($id, $search, $perPage);
    }

    public function getAllMeasuresByStrategicOutputId(int $id, int $perPage = 10)
    {
        return $this->measureRepository->getAllByStrategicOutput($id, $perPage);
    }

    public function getAllPaginatedMeasuresByStrategicOutputId(int $id, ?string $search, int $per_page = 10)
    {
        return $this->measureRepository->getAllPaginatedByStrategicOutput($id, $search, $per_page);
    }

    public function getIndicatorOfMeasureByName(int $measureId, string $indicatorName)
    {
        $measure = $this->measureRepository->findById($measureId);
        if (!$measure) {
            throw new RuntimeException("Measure with ID: {$measureId} not found");
        }
        return $measure->findIndicatorByName($indicatorName);
    }

    public function addIndicatorToMeasure(int $measureId, $indicator): void
    {
        $measure = $this->getMeasureById($measureId);
        if (!$measure) throw new RuntimeException("The measure with id {$measureId} does not exist.");

        $measure->addIndicator($indicator);
    }

    public function removeIndicatorFromMeasure(int $measureId, $indicator): void
    {
        $measure = $this->getMeasureById($measureId);
        $removed = $measure->removeIndicator($indicator->getName());

        if (!$removed) {
            throw new RuntimeException("The specified indicator does not exist in this measure");
        }
    }
    
    public function getByStrategicOutputIds(array $strategicOutputsIds){
        return $this->measureRepository->getByStrategicOutputIds($strategicOutputsIds);
    }

    public function deleteMeasure(int $id): void
    {
        $measure = $this->measureRepository->findById($id);

        $strategicOutput = $this->strategicOutputRepository->findById($measure->strategic_output_id);
        $this->assertCountryNotActiveByStrategicOutput($strategicOutput);

        if ($measure->indicators()->count() > 0) {
            throw new RuntimeException('Cannot delete a measure that has associated indicators.');
        }
        $measure->delete();
    }
}
