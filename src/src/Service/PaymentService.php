<?php

namespace App\Service;

use App\DTO\PaymentData;
use App\DTO\PaymentResult;
use App\Exception\PaymentException;
use App\Service\PaymentGateway\ACIProcessor;
use App\Service\PaymentGateway\Shift4Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentService
{
    private array $processors = [];
    private array $gatewayConfig;
    private ContainerInterface $container;

    public function __construct(array $gatewayConfig, ContainerInterface $container)
    {
        $this->gatewayConfig = $gatewayConfig;
        $this->container = $container;
        $this->initializeGateways();
    }

    private function initializeGateways(): void
    {
        foreach ($this->gatewayConfig as $gateway => $config) {
            if (isset($config['class']) && $config['enabled']) {
                $this->processors[$gateway] = $this->container->get($config['class']);
            }
        }
    }

    /**
     * @throws PaymentException
     */
    public function processPayment(string $processor, PaymentData $paymentData): PaymentResult
    {
        if (!isset($this->processors[$processor])) {
            if (!isset($this->gatewayConfig[$processor])) {
                throw new PaymentException("Payment gateway '$processor' does not exist.");
            }

            if (!$this->gatewayConfig[$processor]['enabled']) {
                throw new PaymentException("Payment gateway '$processor' is disabled.");
            }

            throw new PaymentException("Payment gateway '$processor' error.");
        }

        return $this->processors[$processor]->processPayment($paymentData);
    }
}