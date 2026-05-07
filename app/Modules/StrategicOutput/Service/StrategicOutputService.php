<?php
namespace App\Modules\StrategicOutput\Service;

use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;
use RuntimeException;

class StrategicOutputService{

    private StrategicOutputRepository $strategicOutputRepository;
    private CountryKpaRepository $countryKpaRepository;
    
    public function __construct(StrategicOutputRepository $strategicOutputRepository, CountryKpaRepository $countryKpaRepository)
    {
        $this->strategicOutputRepository = $strategicOutputRepository;
        $this->countryKpaRepository = $countryKpaRepository;
    }

    public function getAllStrategicOutputs()
    {
        return $this->strategicOutputRepository->getAll();
    }

    public function getStrategicOutputById(int $id): StrategicOutput
    {
        $strategicOutput = $this->strategicOutputRepository->findById($id);
        if (!$strategicOutput) throw new \RuntimeException("The strategic result with id {$id} does not exist");
        return $strategicOutput;
    }

    public function findStrategicOutputByName(string $name): StrategicOutput
    {
        $strategicOutput = $this->strategicOutputRepository->findByName($name);
        if (!$strategicOutput) throw new \RuntimeException("Strategic result with name {$name} does not exist");
        
        return $strategicOutput;
    }

    private function assertCountryNotActive(int $idCk): void
    {
        $countryKpa = $this->countryKpaRepository->getById($idCk);
        if ($countryKpa->country->active) {
            throw new \RuntimeException('Cannot add or remove strategic outputs while the country is active.');
        }
    }

    public function createStrategicOutput(string $name, int $idCk): StrategicOutput
    {
        $this->assertCountryNotActive($idCk);

        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        if ($this->strategicOutputRepository->existsByNameAndCountryKpa($normalizedName, $idCk)) throw new \RuntimeException("StrategicOutput with name {$normalizedName} already exists assigned in the Kpa");
        
        $strategicOutput = StrategicOutput::at($name);
        $strategicOutput->id_ck = $idCk;

        $this->strategicOutputRepository->save($strategicOutput);
        return $strategicOutput;
    }

    public function getByCountryKpaId(int $id, int $perPage = 10)
    {
        return $this->strategicOutputRepository->getByCountryKpa($id, $perPage);
    }


    public function updateStrategicOutput(int $id, string $name, ?int $idCk = null): StrategicOutput
    {
        $strategicOutput = $this->getStrategicOutputById($id);
        if (!$strategicOutput) throw new \RuntimeException("The StrategicOutput with id {$id} does not exist");

        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        $existing = $this->strategicOutputRepository->existsByNameAndCountryKpa($normalizedName, $idCk);
        
        if ($existing && $existing->id !== $id) throw new \RuntimeException("StrategicOutput with name {$normalizedName} already exists assigned in the Kpa");

        $dataToUpdate = ['name' => $normalizedName];

        if ($idCk !== null) {
            $dataToUpdate['id_ck'] = $idCk;
        }
        
        $strategicOutput->update($dataToUpdate);
        $this->strategicOutputRepository->save($strategicOutput);
        return $strategicOutput;
    }

    public function addMeasureToStrategicOutput(int $strategicOutputId, $measure)
    {
        $strategicOutput = $this->getStrategicOutputById($strategicOutputId);
        if (!$strategicOutput) throw new \RuntimeException("Strategic output with id {$strategicOutputId} does not exist");

        $strategicOutput->addMeasure($measure);
    }

    public function removeMeasureFromStrategicOutput(int $strategicOutputId, $measure)
    {
        $strategicOutputId = $this->getStrategicOutputById($strategicOutputId);
        $removed = $strategicOutputId->removeMeasure($measure->getName());

        if (!$removed) throw new \RuntimeException("The specified indicator does not exist in this measure");
    }

    public function getStrategicOutputsByKpaId(int $perPage, ?string $search, int $kpaId){
        $countryKpaIds = $this->countryKpaRepository->getIdsByKpaId($kpaId)->toArray();

        if(empty($countryKpaIds)) return $this->strategicOutputRepository->paginateByCountryKpaIds([], $search, $perPage);
        return $this->strategicOutputRepository->paginateByCountryKpaIds($countryKpaIds, $search, $perPage);

    }

    public function getStrategicOutputsByCountryKpaIds(array $countryKpaIds, ?string $search, int $perPage)
    {
        return $this->strategicOutputRepository->paginateByCountryKpaIds($countryKpaIds, $search, $perPage);
    }

    public function getByCountryKpaIds(array $countryKpaIds)
    {
        return $this->strategicOutputRepository->getByCountryKpaIds($countryKpaIds);
    }

    public function deleteStrategicOutput(int $id): void
    {
        $strategicOutput = $this->strategicOutputRepository->findById($id);

        $strategicOutput->load('countryKpa.country');
        if ($strategicOutput->countryKpa->country->active) {
            throw new RuntimeException('Cannot add or remove strategic outputs while the country is active.');
        }

        if ($strategicOutput->measures()->count() > 0) {
            throw new RuntimeException('Cannot delete a strategic output that has associated measures.');
        }
        $strategicOutput->delete();
    }
}
