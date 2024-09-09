<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class CardNumber extends Constraint
{
    public $network = 'The credit card network is not supported, supported networks are VISA, MAESTRO, MASTERCARD, AMERICAN EXPRESS, DISCOVER, JCB.';
    public $invalidNumber = 'The credit card number is invalid.';
}