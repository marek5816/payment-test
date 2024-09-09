<?php

namespace App\Service\PaymentGateway;

use App\DTO\PaymentData;
use App\DTO\PaymentResult;
use App\Exception\PaymentException;
use App\Utils\CardNetworks;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ACIProcessor {
    private HttpClientInterface $client;

    public function __construct() {
        $this->client = HttpClient::create([
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['ACI_BEARER'],
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'verify_peer' => $_ENV['APP_ENV'] === 'prod'
        ]);
    }

    /**
     * @throws PaymentException
     */
    public function processPayment(PaymentData $paymentData): PaymentResult {
        /*
         * TODO - TEST NOTE
         * The test enviroment accept only EUR currency, but I did not wanted to hardcode it
         */
        if ($paymentData->currency !== PaymentData::EUR) {
            throw new PaymentException('ACI supports only EUR currency');
        }

        $response = $this->sendChargeByCard($paymentData);

        $decodedResponse = json_decode($response, true);
        return $this->mapResponseToPaymentResult($decodedResponse);
    }

    /**
     * @throws PaymentException
     */
    private function sendChargeByCard(PaymentData $paymentData): string {
        $url = "https://eu-test.oppwa.com/v1/payments";

        $network = CardNetworks::getNetworkFromCardNumber($paymentData->cardNumber);
        $paymentBrand = $this->mapNetworkToBrand($network);

        $params = [
            "entityId" => $_ENV['ACI_ENTITY_ID'],
            "amount" => $paymentData->amount,
            "currency" => $paymentData->currency,
            "paymentBrand" => $paymentBrand,
            "paymentType" => "PA",
            "card.number" => $paymentData->cardNumber,
            "card.expiryMonth" => $paymentData->cardExpMonth,
            "card.expiryYear" => $paymentData->cardExpYear,
            "card.cvv" => $paymentData->cardCvv,
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'body' => $params
            ]);

            return $response->getContent();
        } catch (\Throwable $e) {
            throw new PaymentException('There is an error with the ACI API: ' . $e->getMessage());
        }
    }

    private function mapResponseToPaymentResult(array $response): PaymentResult
    {
        $paymentResult = new PaymentResult();
        $paymentResult->resultCode = $response['result']['code'];
        $paymentResult->resultMessage = $response['result']['description'];

        if (!$this->checkSuccessfulCode($paymentResult->resultCode)) {
            $paymentResult->error = true;
            return $paymentResult;
        }

        $paymentResult->id = $response['id'];
        $paymentResult->amount = $response['amount'];
        $paymentResult->currency = $response['currency'];
        $paymentResult->cardBin = $response['card']['bin'];

        $dateTime = new \DateTime($response['timestamp'], new \DateTimeZone('UTC'));
        $paymentResult->unixTimestamp = $dateTime->getTimestamp();

        return $paymentResult;
    }

    /**
     * @throws PaymentException
     */
    private function mapNetworkToBrand($network): string
    {
        return match ($network) {
            CardNetworks::VISA => 'VISA',
            CardNetworks::MAESTRO => 'MAESTRO',
            CardNetworks::MASTERCARD => 'MASTER',
            CardNetworks::AMERICAN_EXPRESS => 'AMEX',
            CardNetworks::DISCOVER => 'DISCOVER',
            CardNetworks::JCB => 'JCB',
            default => throw new PaymentException('The credit card network is not supported.'),
        };
    }

    private function checkSuccessfulCode($code): bool
    {
        $patterns = [
            '/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[1][12]0)/',
            '/^(000\.400\.0[^3]|000\.400\.100)/',
            '/^(000\.200)/',
            '/^(800\.400\.5|100\.400\.500)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $code)) {
                return true;
            }
        }

        return false;
    }
}