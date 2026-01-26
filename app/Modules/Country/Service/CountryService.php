<?php

namespace App\Modules\Country\Service;

use App\Modules\Country\Domain\Country;
use App\Modules\Country\Repository\CountryRepository;
use App\Modules\Currency\Domain\Currency;
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

    public function createCountry(string $name, $currency): Country
    {
        $name = trim($name);
        $currencyCode = strtoupper(trim($currency['code']));

        if ($this->countryRepository->existsByName($name)) {
            throw new RuntimeException("The country {$name} already exists");
        }

        if (!empty($currency['id'])) {
            $currency = $this->currencyRepository->findById($currency['id']);
            if (!$currency) {
                throw new RuntimeException("The specified currency does not exist");
            }
        } else {
            $currency = $this->currencyRepository->findByCode($currencyCode);
            if ($currency == null) {
                $currency = Currency::at($currencyCode); 
                $this->currencyRepository->save($currency);
            }
        }

        $country = Country::at($name, $currency);
        $this->countryRepository->save($country);
        return $country;

    }

    public function updateCountry(int $id, string $name, $currency): Country
    {
        $country = $this->countryRepository->findById($id);

        try {
            $existingCountry = $this->countryRepository->findBy('name', trim($name));
            if ($existingCountry && $existingCountry->id !== $id) {
                throw new RuntimeException("The country {$name} already exists");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'not found')) {
                throw $e; 
            }
        }

        $currencyCode = strtoupper(trim($currency['code']));

        if (!empty($currency['id'])) {
            $currency = $this->currencyRepository->findById($currency['id']);
            if (!$currency) {
                throw new RuntimeException("The specified currency does not exist");
            }
        } else {
            $currency = $this->currencyRepository->findByCode($currencyCode);
            if ($currency == null) {
                $currency = Currency::at($currencyCode); 
                $this->currencyRepository->save($currency);
            }
        }

        $updatedCountry = Country::at($name, $currency);
        $country->name = $updatedCountry->name;
        $country->currency_id = $updatedCountry->currency_id;
        
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

    public function getAllCountries(?string $search, int $perPage = 10)
    {
        return $this->countryRepository->getPaginated($search, $perPage);
    }

        public function getAllCountriesWithKpasNumber(?string $search, int $perPage = 10)
    {
        return $this->countryRepository->getPaginatedWithKpasNumber($search, $perPage);
    }

    public function countryExists(string $name): bool
    {
        return $this->countryRepository->exists('name', trim($name));
    }
}
