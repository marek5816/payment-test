<?php

namespace App\Utils;

class CardNetworks {
    /*
     * TODO - TEST NOTE
     * For test scenario I only included few card networks
     * Also researching all networks IIN ranges can take lot of time, so for test reason, I made check only for some of them from wikipedia
     * https://en.wikipedia.org/wiki/Payment_card_number
     */

    public const UNKNOWN = 0;
    public const VISA = 10;
    public const MAESTRO = 20;
    public const MASTERCARD = 30;
    public const AMERICAN_EXPRESS = 40;
    public const DISCOVER = 50;
    public const JCB = 60;

    public static function getNetworkFromCardNumber($number)
    {
        $visaPrefixe = '/^4(?!903|905|911|936)/';
        if (preg_match($visaPrefixe, $number)) {
            return self::VISA;
        }

        $maestroPrefixes = '/^(6759|676770|676774|5018|5020|5038|5893|6304|6759|6761|6762|6763)/';
        if (preg_match($maestroPrefixes, $number)) {
            return self::MAESTRO;
        }

        $mastercardPrefixes = '/^(222[1-9]|22[3-9][0-9]|2[3-6]\d{2}|27[0-1][0-9]|2720|5[1-5])/';
        if (preg_match($mastercardPrefixes, $number)) {
            return self::MASTERCARD;
        }

        $americanExpressPrefixes = '/^3[47]/';
        if (preg_match($americanExpressPrefixes, $number)) {
            return self::AMERICAN_EXPRESS;
        }

        $discoverPrefixes = '/^(64[4-9]|62212[6-9]|6221[3-9]\d|622[2-8]\d{2}|6229[01]\d|62292[0-5]|6011|65)/';
        if (preg_match($discoverPrefixes, $number)) {
            return self::DISCOVER;
        }

        $jcbPrefixes = '/^(352[8-9]|35[3-8]\d)/';
        if (preg_match($jcbPrefixes, $number)) {
            return self::JCB;
        }

        return self::UNKNOWN;
    }
}