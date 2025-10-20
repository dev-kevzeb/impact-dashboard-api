<?php

namespace App\Services;

use App\Repositories\CurrencyRepository;

class CurrencyService
{
    protected CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function getAllCurrencies()
    {
        return $this->currencyRepository->getAll();
    }

    public function getCurrencyById(int $id)
    {
        return $this->currencyRepository->getById($id);
    }

    public function createCurrency(array $data)
    {
        return $this->currencyRepository->create($data);
    }

    public function updateCurrency(int $id, array $data)
    {
        return $this->currencyRepository->update($id, $data);
    }

    public function deleteCurrency(int $id)
    {
        return $this->currencyRepository->delete($id);
    }
}
