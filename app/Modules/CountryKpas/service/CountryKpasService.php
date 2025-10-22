<?php

namespace App\Modules\CountryKpas\service;

use App\Modules\CountryKpas\Repository\CountryKpasRepository;

class CountryKpasService
{
    protected CountryKpasRepository $repo;

    public function __construct(CountryKpasRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list()
    {
        return $this->repo->getAll();
    }

    public function get(int $id)
    {
        return $this->repo->getById($id);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function attach(int $countryId, int $kpaId)
    {
        return $this->repo->attachKpaToCountry($countryId, $kpaId);
    }

    public function detach(int $countryId, int $kpaId)
    {
        return $this->repo->detachKpaFromCountry($countryId, $kpaId);
    }
}
