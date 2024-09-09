<?php

namespace App\Command;

use App\DTO\PaymentData;
use App\DTO\PaymentResult;
use App\Exception\PaymentException;
use App\Service\PaymentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'payment:request',
    description: 'Process a payment.'
)]
class PaymentCommand extends Command {
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService) {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('gateway', InputArgument::REQUIRED, 'Payment gateway')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Amount to be paid')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Currency of the transaction')
            ->addOption('card_number', null, InputOption::VALUE_REQUIRED, 'Credit card number')
            ->addOption('card_exp_year', null, InputOption::VALUE_REQUIRED, 'Card expiration year')
            ->addOption('card_exp_month', null, InputOption::VALUE_REQUIRED, 'Card expiration month')
            ->addOption('card_cvv', null, InputOption::VALUE_REQUIRED, 'Card cvv code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $paymentData = new PaymentData();
            $paymentData->fillFromCLI($input);
            $paymentResult = $this->paymentService->processPayment($input->getArgument('gateway'), $paymentData);
        } catch (PaymentException $e) {
            $this->logPaymentError($io, "", $e->getMessage());
            return Command::FAILURE;
        }

        return $this->handlePaymentResult($io, $paymentResult);
    }

    private function handlePaymentResult(SymfonyStyle $io, PaymentResult $paymentResult): int
    {
        if ($paymentResult->error === true) {
            $this->logPaymentError($io, $paymentResult->resultCode, $paymentResult->resultMessage);
            return Command::FAILURE;
        }

        $this->logPaymentSuccess($io, $paymentResult);
        return Command::SUCCESS;
    }

    private function logPaymentError(SymfonyStyle $io, string $code, string $message): void
    {
        $io->error("Error processing payment.");
        $io->error("Code: " . $code);
        $io->error("Message: " . $message);
    }

    private function logPaymentSuccess(SymfonyStyle $io, $paymentResult): void
    {
        $io->success("Payment processed successfully.");
        $io->info("Code: " . $paymentResult->resultCode);
        $io->info("Message: " . $paymentResult->resultMessage);
        $io->info("Id: " . $paymentResult->id);
        $io->info("Time: " . $paymentResult->unixTimestamp);
        $io->info("Amount: " . $paymentResult->amount);
        $io->info("Currency: " . $paymentResult->currency);
        $io->info("Card Bin: " . $paymentResult->cardBin);
    }
}