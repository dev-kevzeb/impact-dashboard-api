<?php

namespace App\Modules\Currency\Service;

use App\Modules\Currency\Repository\CurrencyRepository;

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
