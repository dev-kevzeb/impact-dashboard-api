<?php

namespace App\Modules\Country\Service;

use App\Modules\Country\Repository\CountryRepository;

class CountryService
{
    protected CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function getAllCountries()
    {
        return $this->countryRepository->getAll();
    }

    public function getCountryById(int $id)
    {
        return $this->countryRepository->getById($id);
    }

    public function createCountry(array $data)
    {
        return $this->countryRepository->create($data);
    }

    public function updateCountry(int $id, array $data)
    {
        return $this->countryRepository->update($id, $data);
    }
    public function deleteCountry(int $id)
    {
        return $this->countryRepository->delete($id);
    }
}
