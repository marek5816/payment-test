<?php

namespace App\Tests\DTO;

use App\DTO\PaymentData;
use App\Exception\PaymentException;
use PHPUnit\Framework\TestCase;

class PaymentDataTest extends TestCase
{
    protected PaymentData $paymentData;

    protected function setUp(): void
    {
        $this->paymentData = new PaymentData();
        $this->paymentData->amount = 100.00;
        $this->paymentData->currency = 'EUR';
        $this->paymentData->cardNumber = '4012000100000007';
        $this->paymentData->cardExpYear = date('Y') + 1;
        $this->paymentData->cardExpMonth = '12';
        $this->paymentData->cardCvv = '123';
    }

    private function validateDataExceptionRegex($message): void
    {
        $this->expectException(PaymentException::class);

        $this->expectExceptionMessageMatches($message);

        $this->paymentData->validateAllClassVariables();
    }

    public function testValidPaymentData()
    {
        try {
            $this->paymentData->validateAllClassVariables();
            $this->assertTrue(true);
        } catch (PaymentException $e) {
            $this->fail("PaymentException was thrown unexpectedly for valid data.");
        }
    }

    public function testExpiredCard()
    {
        $currentMonth = (int)date('m');

        if ($currentMonth === 1) {
            $this->markTestSkipped('This test cannot be run in January, because year can not be from past.');
        }

        $this->paymentData->cardExpYear = date('Y');
        $this->paymentData->cardExpMonth = '01';

        $this->validateDataExceptionRegex('/card is expired/');
    }

    public function testWrongFloatingPoint()
    {
        $this->paymentData->amount = 100.00001;

        $this->validateDataExceptionRegex('/can have a maximum of .* decimal places/');
    }

    public function testInvalidCardNumber()
    {
        $this->paymentData->cardNumber = '4929225021529112';

        $this->validateDataExceptionRegex('/credit card number is invalid/');
    }

    public function testUnsupportedNetwork()
    {
        $this->paymentData->cardNumber = '9792000100000007';

        $this->validateDataExceptionRegex('/card network is not supported/');
    }
}
