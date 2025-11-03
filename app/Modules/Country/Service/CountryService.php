<?php

namespace App\Modules\Country\Service;

use App\Modules\Country\Domain\Country;
use App\Modules\Country\Repository\CountryRepository;
use App\Modules\Currency\Repository\CurrencyRepository;

class CountryService
{
    protected CountryRepository $countryRepository;
    protected CurrencyRepository $currencyRepository;

    public function __construct(
        CountryRepository $countryRepository,
        CurrencyRepository $currencyRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
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
        // Validar duplicados por nombre (normalizado)
        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($data['name'])), MB_CASE_TITLE, "UTF-8");
        
        if ($this->countryRepository->existsByName($normalizedName)) {
            throw new \RuntimeException("El país ya existe: {$normalizedName}");
        }

        // Obtener la moneda
        $currency = $this->currencyRepository->getById($data['currency_id']);
        if (!$currency) {
            throw new \RuntimeException("La moneda no existe");
        }

        // Usar el método at() como constructor (valida y normaliza)
        $country = Country::at($data['name'], $currency);
        $this->countryRepository->save($country);
        
        return $country;
    }

    public function updateCountry(int $id, array $data)
    {
        $country = $this->countryRepository->getById($id);
        if (!$country) {
            throw new \RuntimeException("El país con id {$id} no existe");
        }

        // Validar duplicados por nombre (excepto el mismo registro)
        $normalizedName = mb_convert_case(preg_replace('/\s+/', ' ', trim($data['name'])), MB_CASE_TITLE, "UTF-8");
        $existing = $this->countryRepository->findByName($normalizedName);
        
        if ($existing && $existing->id !== $id) {
            throw new \RuntimeException("El país ya existe: {$normalizedName}");
        }

        return $this->countryRepository->update($id, $data);
    }

    public function deleteCountry(int $id)
    {
        return $this->countryRepository->delete($id);
    }
}
