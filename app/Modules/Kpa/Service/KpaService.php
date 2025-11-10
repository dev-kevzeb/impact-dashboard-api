<?php

namespace App\Modules\Kpa\Service;

use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Kpa\Repository\KpaRepository;
use RuntimeException;

class KpaService
{
    private KpaRepository $kpaRepository;

    public function __construct(KpaRepository $kpaRepository)
    {
        $this->kpaRepository = $kpaRepository;
    }

    public function createKpa(string $name, float $implementation): Kpa
    {
        if ($this->kpaRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un KPA con el nombre: {$name}");
        }

        $kpa = Kpa::at($name, $implementation);
        
        $this->kpaRepository->save($kpa);
        
        return $kpa;
    }

    public function getKpaById(int $id): Kpa
    {
        return $this->kpaRepository->findById($id);
    }

    public function findKpaByName(string $name): Kpa
    {
        return $this->kpaRepository->findBy('name', $name);
    }

    public function getAllKpas()
    {
        return $this->kpaRepository->getAll();
    }

    public function updateKpa(int $id, string $name, float $implementation): Kpa
    {
        $kpa = $this->kpaRepository->findById($id);

        try {
            $existingKpa = $this->kpaRepository->findBy('name', trim($name));
            if ($existingKpa && $existingKpa->id !== $id) {
                throw new RuntimeException("Ya existe otro KPA con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e; 
            }
        }

        $updatedKpa = Kpa::at($name, $implementation);
        $kpa->name = $updatedKpa->name;
        $kpa->implementation = $updatedKpa->implementation;
        
        $this->kpaRepository->save($kpa);
        
        return $kpa;
    }

    public function kpaExists(string $name): bool
    {
        return $this->kpaRepository->exists('name', $name);
    }
}
