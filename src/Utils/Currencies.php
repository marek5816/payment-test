<?php

namespace App\Utils;

use App\Exception\PaymentException;

class Currencies {
    public static array $currencyMinorUnits = [
        'AFN' => 2, 'EUR' => 2, 'ALL' => 2, 'DZD' => 2, 'USD' => 2, 'AOA' => 2, 'XCD' => 2, 'ARS' => 2, 'AMD' => 2, 'AWG' => 2, 'AUD' => 2, 'AZN' => 2, 'BSD' => 2, 'BHD' => 3, 'BDT' => 2, 'BBD' => 2, 'BYN' => 2, 'BZD' => 2, 'XOF' => 0, 'BMD' => 2, 'INR' => 2, 'BTN' => 2, 'BOB' => 2, 'BOV' => 2, 'BAM' => 2, 'BWP' => 2, 'NOK' => 2, 'BRL' => 2, 'BND' => 2, 'BGN' => 2, 'BIF' => 0, 'CVE' => 2, 'KHR' => 2, 'XAF' => 0, 'CAD' => 2, 'KYD' => 2, 'CLP' => 0, 'CLF' => 4, 'CNY' => 2, 'COP' => 2, 'COU' => 2, 'KMF' => 0, 'CDF' => 2, 'NZD' => 2, 'CRC' => 2, 'CUP' => 2, 'CUC' => 2, 'ANG' => 2, 'CZK' => 2, 'DKK' => 2, 'DJF' => 0, 'DOP' => 2, 'EGP' => 2, 'SVC' => 2, 'ERN' => 2, 'SZL' => 2, 'ETB' => 2, 'FKP' => 2, 'FJD' => 2, 'XPF' => 0, 'GMD' => 2, 'GEL' => 2, 'GHS' => 2, 'GIP' => 2, 'GTQ' => 2, 'GBP' => 2, 'GNF' => 0, 'GYD' => 2, 'HTG' => 2, 'HNL' => 2, 'HKD' => 2, 'HUF' => 2, 'ISK' => 0, 'IDR' => 2, 'IRR' => 2, 'IQD' => 3, 'ILS' => 2, 'JMD' => 2, 'JPY' => 0, 'JOD' => 3, 'KZT' => 2, 'KES' => 2, 'KPW' => 2, 'KRW' => 0, 'KWD' => 3, 'KGS' => 2, 'LAK' => 2, 'LBP' => 2, 'LSL' => 2, 'ZAR' => 2, 'LRD' => 2, 'LYD' => 3, 'CHF' => 2, 'MOP' => 2, 'MKD' => 2, 'MGA' => 2, 'MWK' => 2, 'MYR' => 2, 'MVR' => 2, 'MRU' => 2, 'MUR' => 2, 'MXN' => 2, 'MXV' => 2, 'MDL' => 2, 'MNT' => 2, 'MAD' => 2, 'MZN' => 2, 'MMK' => 2, 'NAD' => 2, 'NPR' => 2, 'NIO' => 2, 'NGN' => 2, 'OMR' => 3, 'PKR' => 2, 'PAB' => 2, 'PGK' => 2, 'PYG' => 0, 'PEN' => 2, 'PHP' => 2, 'PLN' => 2, 'QAR' => 2, 'RON' => 2, 'RUB' => 2, 'RWF' => 0, 'SHP' => 2, 'WST' => 2, 'STN' => 2, 'SAR' => 2, 'RSD' => 2, 'SCR' => 2, 'SLE' => 2, 'SGD' => 2, 'SBD' => 2, 'SOS' => 2, 'SSP' => 2, 'LKR' => 2, 'SDG' => 2, 'SRD' => 2, 'SEK' => 2, 'CHE' => 2, 'CHW' => 2, 'SYP' => 2, 'TWD' => 2, 'TJS' => 2, 'TZS' => 2, 'THB' => 2, 'TOP' => 2, 'TTD' => 2, 'TND' => 3, 'TRY' => 2, 'TMT' => 2, 'UGX' => 0, 'UAH' => 2, 'AED' => 2, 'USN' => 2, 'UYU' => 2, 'UYI' => 0, 'UYW' => 4, 'UZS' => 2, 'VUV' => 0, 'VES' => 2, 'VED' => 2, 'VND' => 0, 'YER' => 2, 'ZMW' => 2, 'ZWG' => 2,
    ];

    /**
     * @throws PaymentException
     */
    public static function hasValidFloatingPoint(float $amount, string $currencyCode): bool
    {
        self::checkCurrencyCode($currencyCode);
        $floatingPoints = strlen(substr(strrchr($amount, "."), 1));

        if ($floatingPoints > self::$currencyMinorUnits[$currencyCode]) {
            return false;
        }

        return true;
    }

    /**
     * @throws PaymentException
     */
    public static function toMinorUnits(float $amount, string $currencyCode): int
    {
        self::checkCurrencyCode($currencyCode);
        $minorUnitFactor = pow(10, self::$currencyMinorUnits[$currencyCode]);
        return (int) round($amount * $minorUnitFactor);
    }

    /**
     * @throws PaymentException
     */
    public static function fromMinorUnits(float $amount, string $currencyCode): float
    {
        self::checkCurrencyCode($currencyCode);
        $minorUnitFactor = pow(10, self::$currencyMinorUnits[$currencyCode]);
        return $amount / $minorUnitFactor;
    }

    /**
     * @throws PaymentException
     */
    private static function checkCurrencyCode(string $currencyCode): void
    {
        if (!isset(self::$currencyMinorUnits[$currencyCode])) {
            throw new PaymentException("Unsupported currency code: $currencyCode");
        }
    }
}