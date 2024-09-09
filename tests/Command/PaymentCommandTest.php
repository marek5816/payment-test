<?php

namespace App\Tests\Command;

use App\Command\PaymentCommand;
use App\DTO\PaymentResult;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

class PaymentCommandTest extends KernelTestCase
{
    public function testPaymentRequest()
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('payment:request');

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'gateway' => 'aci',
            '--amount' => '150.05',
            '--currency' => 'EUR',
            '--card_number' => '4111111111111111',
            '--card_exp_year' => '2025',
            '--card_exp_month' => '12',
            '--card_cvv' => '123',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Payment processed successfully.', $output);
        $this->assertStringContainsString('Amount: 150', $output);
        $this->assertStringContainsString('Currency: EUR', $output);
        $this->assertStringContainsString('Card Bin: 411111', $output);
    }
}