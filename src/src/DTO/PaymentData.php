<?php

namespace App\DTO;

use App\Exception\PaymentException;
use App\Utils\Currencies;
use App\Validator\CardNumber;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentData {
    public const EUR = "EUR";

    public float $amount;
    public string $currency;
    public string $cardNumber;
    public string $cardExpYear;
    public string $cardExpMonth;
    public string $cardCvv;

    private ValidatorInterface $validator;

    public function __construct() {
        $this->validator = Validation::createValidator();
    }

    /**
     * Fill class variables from CLI InputInterface.
     *
     * @throws PaymentException
     */
    public function fillFromCLI(InputInterface $input): void {
        $data = [
            'amount' => $input->getOption('amount'),
            'currency' => $input->getOption('currency'),
            'cardNumber' => $input->getOption('card_number'),
            'cardExpYear' => $input->getOption('card_exp_year'),
            'cardExpMonth' => $input->getOption('card_exp_month'),
            'cardCvv' => $input->getOption('card_cvv'),
        ];

        $this->validate($data);
        $this->assignValues($data);
    }

    /**
     * Fill class variables from Request.
     *
     * @throws PaymentException
     */
    public function fillFromRequest(Request $request): void {
        $data = json_decode($request->getContent(), true);

        $this->validate($data);
        $this->assignValues($data);
    }

    public function assignValues(array $data): void
    {
        $this->amount = (float)$data['amount'];
        $this->currency = $data['currency'];
        $this->cardNumber = $data['cardNumber'];
        $this->cardExpYear = $data['cardExpYear'];
        $this->cardExpMonth = $data['cardExpMonth'];
        $this->cardCvv = $data['cardCvv'];
    }

    /**
     * Validates the current class variables using the same validation rules.
     *
     * @throws PaymentException
     */
    public function validateAllClassVariables(): void
    {
        $data = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'cardNumber' => $this->cardNumber,
            'cardExpYear' => $this->cardExpYear,
            'cardExpMonth' => $this->cardExpMonth,
            'cardCvv' => $this->cardCvv,
        ];

        $this->validate($data);
    }

    /**
     * Validate the provided data using Symfony constraints.
     *
     * @throws PaymentException
     */
    public function validate($data): void {
        $constraint = $this->getValidationConstraints();

        $violations = $this->validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $violation = $violations[0];
            $variableName = trim($violation->getPropertyPath(), '[]');

            throw new PaymentException("Error in '$variableName': {$violation->getMessage()}");
        }

        $this->validateCurrencyPrecision($data['amount'], $data['currency']);
        $this->validateCardExpirationDate($data['cardExpYear'], $data['cardExpMonth']);
    }

    private function getValidationConstraints(): Assert\Collection {
        return new Assert\Collection([
            'amount' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'numeric']),
                new Assert\GreaterThanOrEqual(0.01)
            ],
            'currency' => [
                new Assert\NotBlank(),
                new Assert\Currency()
            ],
            'cardNumber' => [
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^\d{8,19}$/',
                    'message' => 'The card number must be in range of 8-19 digits.'
                ]),
                new CardNumber()
            ],
            'cardExpYear' => [
                new Assert\NotBlank(),
                new Assert\Range([
                    'min' => date('Y'),
                    'max' => date('Y') + 10
                ])
            ],
            'cardExpMonth' => [
                new Assert\NotBlank(),
                new Assert\Range([
                    'min' => 01,
                    'max' => 12,
                    'notInRangeMessage' => 'This value should be between 01 and 12, in two-digit format.'
                ]),
                new Assert\Regex([
                    'pattern' => '/^(0[1-9]|1[0-2])$/',
                    'message' => 'The month must be in 2 digits format (e.g. 04 for April).'
                ])
            ],
            'cardCvv' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 4
                ]),
                new Assert\Type([
                    'type' => 'digit'
                ])
            ]
        ]);
    }

    /**
     * @throws PaymentException
     */
    private function validateCurrencyPrecision(float $amount, string $currency): void
    {
        if (!Currencies::hasValidFloatingPoint($amount, $currency)) {
            $floatingPoints = Currencies::$currencyMinorUnits[$currency];
            throw new PaymentException("Currency \"$currency\" can have a maximum of $floatingPoints decimal places.");
        }
    }

    /**
     * @throws PaymentException
     */
    private function validateCardExpirationDate(string $year, string $month): void
    {
        if ($year == date('Y') && $month < date('m')) {
            throw new PaymentException('The card is expired.');
        }
    }
}