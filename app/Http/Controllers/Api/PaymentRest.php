<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use App\Services\Invoice\InvoiceCalculator;

class PaymentRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    
    public function getPaymentData(Request $request)
    {
        try {
            $paymentId = $request->query('id');
            $externalId = $request->query('external_id');

            if ($paymentId) {
                $payment = Payment::with([
                    'invoice'
                ])->find($paymentId);

                if (!$payment) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Payment not found'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $payment,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            if ($externalId) {
                $payment = Payment::with([
                    'invoice'
                ])->where('external_id', $externalId)->first();

                if (!$payment) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Payment not found with given external ID'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $payment,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            $payments = Payment::with([
                'invoice'
            ])->paginate(15);

            $responseData = [
                'payments' => $payments->items(),
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem()
                ]
            ];

            return $this->jsonResponseService->createJsonResponse(
                $responseData,
                'success',
                null,
                null,
                null,
                ['status' => 200]
            );

        } catch (\Exception $e) {
            return $this->jsonResponseService->createJsonResponse(
                null,
                'error',
                [
                    'message' => 'An error occurred while fetching payment data',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }

    public function updatePaymentAmount(Request $request)
    {
        try {
            $externalId = $request->input('paymentExternalId');
            $montant = $request->input('montant');

            if (!$externalId) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'Payment External ID is required'],
                    null,
                    null,
                    ['status' => 400]
                );
            }

            if ($montant === null || !is_numeric($montant) || $montant < 0) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'Valid amount is required'],
                    null,
                    null,
                    ['status' => 400]
                );
            }

            $payment = Payment::where('external_id', $externalId)->with('invoice')->first();

            if (!$payment) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'Payment not found with given external ID'],
                    null,
                    null,
                    ['status' => 404]
                );
            }

            
            $invoiceCalculator = new InvoiceCalculator($payment->invoice);
            $amountDue = $invoiceCalculator->getAmountDue()->getAmount(); 

            
            if ($montant > $amountDue) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'Amount paid exceeds the invoice total due'],
                    null,
                    null,
                    ['status' => 400]
                );
            }

            $payment->amount = $montant;
            $payment->save();

            return $this->jsonResponseService->createJsonResponse(
                $payment,
                'success',
                ['message' => 'Payment amount updated successfully'],
                null,
                null,
                ['status' => 200]
            );

        } catch (\Exception $e) {
            return $this->jsonResponseService->createJsonResponse(
                null,
                'error',
                [
                    'message' => 'An error occurred while updating payment amount',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }

    public function deletePayment(Request $request)
    {
        try {
            $externalId = $request->input('external_id');

            if (!$externalId) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'External ID is required'],
                    null,
                    null,
                    ['status' => 400]
                );
            }

            $payment = Payment::where('external_id', $externalId)->first();

            if (!$payment) {
                return $this->jsonResponseService->createJsonResponse(
                    null,
                    'error',
                    ['message' => 'Payment not found with given external ID'],
                    null,
                    null,
                    ['status' => 404]
                );
            }

            $payment->delete();

            return $this->jsonResponseService->createJsonResponse(
                null,
                'success',
                ['message' => 'Payment deleted successfully'],
                null,
                null,
                ['status' => 200]
            );
        } catch (\Exception $e) {
            return $this->jsonResponseService->createJsonResponse(
                null,
                'error',
                [
                    'message' => 'An error occurred while deleting payment',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}