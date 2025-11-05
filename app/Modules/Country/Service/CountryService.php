<?php

namespace App\Modules\Country\Service;

use App\Modules\Country\Domain\Country;
use App\Modules\Country\Repository\CountryRepository;
use App\Modules\Currency\Repository\CurrencyRepository;
use RuntimeException;

class CountryService
{
    private CountryRepository $countryRepository;
    private CurrencyRepository $currencyRepository;

    public function __construct(
        CountryRepository $countryRepository,
        CurrencyRepository $currencyRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
    }

    public function createCountry(string $name, int $currencyId): Country
    {
        if ($this->countryRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un país con el nombre: {$name}");
        }

        $currency = $this->currencyRepository->findById($currencyId);
        
        $country = Country::at($name, $currency);
        
        $this->countryRepository->save($country);
        
        return $country;
    }

    public function getCountryById(int $id): Country
    {
        return $this->countryRepository->findById($id);
    }

    public function findCountryByName(string $name): Country
    {
        return $this->countryRepository->findBy('name', trim($name));
    }

    public function getAllCountries()
    {
        return $this->countryRepository->getAll();
    }

    public function updateCountry(int $id, string $name, int $currencyId): Country
    {
        $country = $this->countryRepository->findById($id);

        try {
            $existingCountry = $this->countryRepository->findBy('name', trim($name));
            if ($existingCountry && $existingCountry->id !== $id) {
                throw new RuntimeException("Ya existe otro país con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e; 
            }
        }

        $currency = $this->currencyRepository->findById($currencyId);
        
        $updatedCountry = Country::at($name, $currency);
        $country->name = $updatedCountry->name;
        $country->currency_id = $updatedCountry->currency_id;
        
        $this->countryRepository->save($country);
        
        return $country;
    }

    public function countryExists(string $name): bool
    {
        return $this->countryRepository->exists('name', trim($name));
    }
}
