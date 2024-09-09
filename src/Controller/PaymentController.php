<?php

namespace App\Controller;

use App\DTO\PaymentData;
use App\DTO\PaymentResult;
use App\Exception\PaymentException;
use App\Service\PaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    #[Route('/payment/request/{gateway}', name: 'payment_process', methods: ['POST'])]
    public function index(Request $request, string $gateway)
    {
        try {
            $paymentData = new PaymentData();
            $paymentData->fillFromRequest($request);
            $paymentResult = $this->paymentService->processPayment($gateway, $paymentData);
        } catch (PaymentException $e) {
            return $this->responsePaymentError("", $e->getMessage());
        }


        return $this->handlePaymentResult($paymentResult);
    }

    private function handlePaymentResult(PaymentResult $paymentResult): JsonResponse
    {
        if ($paymentResult->error === true) {
            return $this->responsePaymentError($paymentResult->resultCode, $paymentResult->resultMessage);
        }

        return $this->responsePaymentSuccess($paymentResult);
    }

    private function responsePaymentError(string $code, string $message): JsonResponse
    {
        return new JsonResponse([
            "error" => [
                "info" => "Error processing payment.",
                "code" => $code,
                "message" => $message,
            ]
        ], 400);
    }

    private function responsePaymentSuccess(PaymentResult $paymentResult): JsonResponse
    {
        return new JsonResponse([
            "info" => "Payment processed successfully.",
            "code" => $paymentResult->resultCode,
            "message" => $paymentResult->resultMessage,
            "id" => $paymentResult->id,
            "time" => $paymentResult->unixTimestamp,
            "amount" => $paymentResult->amount,
            "currency" => $paymentResult->currency,
            "cardBin" => $paymentResult->cardBin,
        ], 200);
    }
}