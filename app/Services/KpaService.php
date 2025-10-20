<?php

namespace App\Services;

use App\Repositories\KpaRepository;

class KpaService
{
    protected KpaRepository $kpaRepository;

    public function __construct(KpaRepository $kpaRepository)
    {
        $this->kpaRepository = $kpaRepository;
    }

    public function getAllKpas()
    {
        return $this->kpaRepository->getAll();
    }

    public function getKpaById(int $id)
    {
        return $this->kpaRepository->getById($id);
    }

    public function createKpa(array $data)
    {
        return $this->kpaRepository->create($data);
    }

    public function updateKpa(int $id, array $data)
    {
        return $this->kpaRepository->update($id, $data);
    }
    public function deleteKpa(int $id)
    {
        return $this->kpaRepository->delete($id);
    }
}
