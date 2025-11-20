<?php
namespace App\Modules\StrategicOutput\Service;

use App\Modules\StrategicOutput\Domain\StrategicOutput;
use App\Modules\StrategicOutput\Repository\StrategicOutputRepository;

class StrategicOutputService{

    private StrategicOutputRepository $strategicOutputRepository;
    
    public function __construct(StrategicOutputRepository $strategicOutputRepository)
    {
        $this->strategicOutputRepository = $strategicOutputRepository;
    }

    public function getAllStrategicOutputs()
    {
        return $this->strategicOutputRepository->getAll();
    }

    public function getStrategicOutputById(int $id): StrategicOutput
    {
        $strategicOutput = $this->strategicOutputRepository->findById($id);
        if (!$strategicOutput) throw new \RuntimeException("El resultado estratégico con id {$id} no existe.");
        
        return $strategicOutput;
    }

    public function createStrategicOutput(string $name, int $idCk): StrategicOutput
    {
        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        if ($this->strategicOutputRepository->existsByName($normalizedName)) throw new \RuntimeException("El StrategicOutput ya existe: {$normalizedName}");
        
        $strategicOutput = StrategicOutput::at($name);
        $strategicOutput->id_ck = $idCk;

        $this->strategicOutputRepository->save($strategicOutput);
        return $strategicOutput;
    }

    public function updateStrategicOutput(int $id, string $name, ?int $idCk = null): StrategicOutput
    {
        $strategicOutput = $this->getStrategicOutputById($id);
        if (!$strategicOutput) throw new \RuntimeException("El StrategicOutput con id {$id} no existe.");

        // Normalizar para comparar duplicados
        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($name)), MB_CASE_TITLE, "UTF-8");
        $existing = $this->strategicOutputRepository->findByName($normalizedName);
        
        if ($existing && $existing->id !== $id) throw new \RuntimeException("El StrategicOutput ya existe: {$normalizedName}");

        $dataToUpdate = ['name' => $normalizedName];

        if ($idCk !== null) {
            $dataToUpdate['id_ck'] = $idCk;
        }
        
        $strategicOutput->update($dataToUpdate);
        $this->strategicOutputRepository->save($strategicOutput);
        return $strategicOutput;
    }

    
}
