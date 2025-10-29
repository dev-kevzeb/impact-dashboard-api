<?php

namespace App\Modules\CountryKpa\Service;

use App\Modules\CountryKpa\Repository\CountryKpaRepository;
use RuntimeException;

class CountryKpaService
{
    protected CountryKpaRepository $repo;

    public function __construct(CountryKpaRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): object
    {
        return $this->repo->getById($id);
    }

    public function create(array $data): object
    {
        // basic validation could be added here if needed
        return $this->repo->create($data);
    }

    public function delete(int $id): ?object
    {
        return $this->repo->delete($id);
    }

    public function attach(int $countryId, int $kpaId): object
    {
        return $this->repo->attachKpaToCountry($countryId, $kpaId);
    }

    public function detach(int $countryId, int $kpaId): int
    {
        return $this->repo->detachKpaFromCountry($countryId, $kpaId);
    }
}
