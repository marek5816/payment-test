<?php

namespace App\Tests\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ACITest extends WebTestCase {

    public function testSuccessfulPayment()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/payment/request/aci',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'cardNumber' => '4012000100000007',
                'cardExpMonth' => '12',
                'cardExpYear' => '2024',
                'cardCvv' => '123',
                'amount' => 100.05,
                'currency' => 'EUR',
            ])
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Payment processed successfully.', $responseContent['info']);
        $this->assertEquals('401200', $responseContent['cardBin']);
        $this->assertEquals(100.05, $responseContent['amount']);
        $this->assertEquals('EUR', $responseContent['currency']);
    }

    public function testUnsupportedCurrency()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/payment/request/aci',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'cardNumber' => '4012000100000007',
                'cardExpMonth' => '12',
                'cardExpYear' => '2024',
                'cardCvv' => '123',
                'amount' => 100.00,
                'currency' => 'USD',
            ])
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $this->assertJson($client->getResponse()->getContent());

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertMatchesRegularExpression('/ACI supports only .* currency/', $responseContent['error']['message']);
    }
}