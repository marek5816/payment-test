<?php

namespace App\Validator;

use App\Utils\CardNetworks;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CardNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$this->validateNetwork($value)) {
            $this->context->buildViolation($constraint->network)
                ->addViolation();
            return;
        }

        if (!$this->validateLuhn($value)) {
            $this->context->buildViolation($constraint->invalidNumber)
                ->addViolation();
        }
    }

    private function validateNetwork($number): bool {
        if (CardNetworks::getNetworkFromCardNumber($number) !== CardNetworks::UNKNOWN) {
            return true;
        }

        return false;
    }

    private function validateLuhn(string $number): bool
    {
        if (!preg_match('/^\d+$/', $number)) {
            return false;
        }

        $sum = 0;
        $flag = 0;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $add = $flag++ & 1 ? $number[$i] * 2 : $number[$i];
            $sum += $add > 9 ? $add - 9 : $add;
        }

        return $sum % 10 === 0;
    }
}