<?php

namespace App\Service\PaymentGateway;

use App\DTO\PaymentData;
use App\DTO\PaymentResult;
use App\Exception\PaymentException;
use App\Utils\Currencies;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Shift4Processor {
    private HttpClientInterface $client;

    public function __construct() {
        $this->client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($_ENV['SHIFT4_SECRET_KEY'] . ':')
            ],
            'verify_peer' => $_ENV['APP_ENV'] === 'prod'
        ]);
    }

    /**
     * @throws PaymentException
     */
    public function processPayment(PaymentData $paymentData): PaymentResult {
        $response = $this->sendChargeByCard($paymentData);
        $decodedResponse = json_decode($response, true);

        return $this->mapResponseToPaymentResult($decodedResponse);
    }

    /**
     * @throws PaymentException
     */
    private function sendChargeByCard(PaymentData $paymentData): string {
        $url = "https://api.shift4.com/charges";

        $amount = Currencies::toMinorUnits($paymentData->amount, $paymentData->currency);

        $data = [
            "amount" => $amount,
            "currency" => $paymentData->currency,
            "card" => [
                "number" => $paymentData->cardNumber,
                "expMonth" => $paymentData->cardExpMonth,
                "expYear" => $paymentData->cardExpYear,
                "cvc" => $paymentData->cardCvv
            ]
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'body' => $data
            ]);

            return $response->getContent();
        } catch (\Throwable $e) {
            throw new PaymentException('There is an error with the ACI API: ' . $e->getMessage());
        }
    }

    private function mapResponseToPaymentResult(array $response): PaymentResult
    {
        $paymentResult = new PaymentResult();
        $paymentResult->resultCode = "";
        $paymentResult->resultMessage = "";

        if (isset($response['error'])) {
            $paymentResult->error = true;
            $paymentResult->resultCode = $response['error']['code'];
            $paymentResult->resultMessage = $response['error']['message'];
            return $paymentResult;
        }

        if ($response['status'] == "failed") {
            $paymentResult->error = true;
            $paymentResult->resultMessage = "Payment failed, unknown error.";
            return $paymentResult;
        }

        $paymentResult->id = $response['id'];
        $paymentResult->unixTimestamp = $response['created'];
        $paymentResult->amount = Currencies::fromMinorUnits($response['amount'], $response['currency']);
        $paymentResult->currency = $response['currency'];
        $paymentResult->cardBin = $response['card']['first6'];

        return $paymentResult;
    }
}