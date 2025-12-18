<?php

namespace App\Modules\Currency\Service;

use App\Modules\Currency\Domain\Currency;
use App\Modules\Currency\Repository\CurrencyRepository;
use RuntimeException;

class CurrencyService
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function createCurrency(string $code): Currency
    {
        $normalizedCode = strtoupper(trim($code));
        
        if ($this->currencyRepository->exists('code', $normalizedCode)) {
            throw new RuntimeException("There is already a coin with the code: {$normalizedCode}");
        }

        $currency = Currency::at($code);
        
        $this->currencyRepository->save($currency);
        
        return $currency;
    }

    public function getCurrencyById(int $id): Currency
    {
        return $this->currencyRepository->findById($id);
    }

    public function findCurrencyByCode(string $code): Currency
    {
        return $this->currencyRepository->findBy('code', strtoupper(trim($code)));
    }

    public function getAllCurrencies()
    {
        return $this->currencyRepository->getAll();
    }

    public function updateCurrency(int $id, string $code): Currency
    {
        $currency = $this->currencyRepository->findById($id);
        
        $normalizedCode = strtoupper(trim($code));

        try {
            $existingCurrency = $this->currencyRepository->findBy('code', $normalizedCode);
            if ($existingCurrency && $existingCurrency->id !== $id) {
                throw new RuntimeException("There is already another currency with the code: {$normalizedCode}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'Not found')) {
                throw $e; 
            }
        }

        $updatedCurrency = Currency::at($code);
        $currency->code = $updatedCurrency->code;
        
        $this->currencyRepository->save($currency);
        
        return $currency;
    }

    public function currencyExists(string $code): bool
    {
        return $this->currencyRepository->exists('code',$code);
    }
}
