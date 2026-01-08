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
            throw new RuntimeException("A KPA already exists with the name: {$name}");
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

    public function getAllKpas(int $perPage = 10)
    {
        return $this->kpaRepository->paginate($perPage);
    }

    public function updateKpa(int $id, string $name, float $implementation): Kpa
    {
        $kpa = $this->kpaRepository->findById($id);

        try {
            $existingKpa = $this->kpaRepository->findBy('name', trim($name));
            if ($existingKpa && $existingKpa->id !== $id) {
                throw new RuntimeException("There is already another KPA with the name: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'Not found')) {
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

    public function getKpasPaginated(?string $search, int $perPage){
        return $this->kpaRepository->getPaginated($search, $perPage);
    }
}
