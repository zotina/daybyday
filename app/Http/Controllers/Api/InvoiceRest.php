<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use App\Services\Invoice\InvoiceCalculator;

class InvoiceRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * Retrieve invoice data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function getMontantDue(Request $request)
     {
         try {
             $externalId = $request->query('external_id');
 
             // Validation de l'entrée
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
 
             // Recherche de l'invoice
             $invoice = Invoice::where('external_id', $externalId)->first();
 
             if (!$invoice) {
                 return $this->jsonResponseService->createJsonResponse(
                     null,
                     'error',
                     ['message' => 'Invoice not found with given external ID'],
                     null,
                     null,
                     ['status' => 404]
                 );
             }
 
             // Calcul du montant dû avec InvoiceCalculator
             $invoiceCalculator = new InvoiceCalculator($invoice);
             $amountDue = $invoiceCalculator->getAmountDue();
 
             return $this->jsonResponseService->createJsonResponse(
                 $amountDue,
                 'success',
                 ['message' => 'Amount due retrieved successfully'],
                 null,
                 null,
                 ['status' => 200]
             );
 
         } catch (\Exception $e) {
             return $this->jsonResponseService->createJsonResponse(
                 null,
                 'error',
                 [
                     'message' => 'An error occurred while fetching amount due',
                     'details' => $e->getMessage()
                 ],
                 null,
                 null,
                 ['status' => 500]
             );
         }
     }

    public function getInvoiceData(Request $request)
    {
        try {
            $invoiceId = $request->query('id');
            $externalId = $request->query('external_id');

            if ($invoiceId) {
                $invoice = Invoice::with([
                    'client',
                    'invoiceLines',
                    'offer',
                    'source',
                    'payments'
                ])->find($invoiceId);

                if (!$invoice) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Invoice not found'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $invoice,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            if ($externalId) {
                $invoice = Invoice::with([
                    'client',
                    'invoiceLines',
                    'offer',
                    'source',
                    'payments'
                ])->where('external_id', $externalId)->first();

                if (!$invoice) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Invoice not found with given external ID'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $invoice,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            $invoices = Invoice::with([
                'client',
                'invoiceLines',
                'offer',
                'source',
                'payments'
            ])->paginate(15);

            $responseData = [
                'invoices' => $invoices->items(),
                'pagination' => [
                    'total' => $invoices->total(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem()
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
                    'message' => 'An error occurred while fetching invoice data',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}