<?php

namespace App\DTO;

use App\Validator\CardNumber;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class PaymentResult {
    public bool $error = false;

    public string $resultCode;
    public string $resultMessage;

    public string $id;
    public string $unixTimestamp;
    public float $amount;
    public string $currency;
    public string $cardBin;
}