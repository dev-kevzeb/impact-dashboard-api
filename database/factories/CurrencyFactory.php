<?php

namespace Database\Factories;

use App\Modules\Currency\Domain\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {

        $validCodes = [
            'EUR',
            'GBP',
            'JPY',
            'CHF',
            'CAD',
            'AUD',
            'CNY',
            'BOB',
            'BRL',
            'ARS',
            'PEN',
            'CLP',
            'COP',
            'UYU',
            'PYG',
            'VES',
            'CRC',
            'GTQ',
            'HNL',
            'NIO',
            'PAB',
            'DOP',
            'CUP',
            'HTG',
            'JMD',
            'TTD',
            'BBD',
            'MXN',
            'KRW',
            'SGD',
            'HKD',
            'THB',
            'MYR',
            'IDR',
            'PHP',
            'VND',
            'INR',
            'PKR',
            'BDT',
            'LKR',
            'NPR',
            'BTN',
            'RUB',
            'UAH',
            'PLN',
            'CZK',
            'HUF',
            'RON',
            'BGN',
            'HRK',
            'SEK',
            'NOK',
            'DKK',
            'ISK',
            'ZAR',
            'EGP',
            'NGN',
            'KES',
            'GHS',
            'MAD',
            'TND',
            'DZD',
            'XOF',
            'XAF',
            'ETB',
            'UGX',
            'TZS',
            'RWF',
            'ZMW',
            'BWP',
            'NAD',
            'SZL',
            'LSL',
            'MWK',
            'MZN',
            'AOA',
            'CVE',
            'GMD',
            'GNF',
            'LRD',
            'SLL',
            'STN',
            'SOL'
        ];

        return [
            'code' => $this->faker->unique()->randomElement($validCodes),
        ];
    }
}
