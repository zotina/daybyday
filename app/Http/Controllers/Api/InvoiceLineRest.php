<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLine;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;

class InvoiceLineRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    
    public function getInvoiceLineData(Request $request)
    {
        try {
            $invoiceLineId = $request->query('id');
            $externalId = $request->query('external_id');

            if ($invoiceLineId) {
                $invoiceLine = InvoiceLine::with([
                    'tasks',
                    'invoice',
                    'product'
                ])->find($invoiceLineId);

                if (!$invoiceLine) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Invoice line not found'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $invoiceLine,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            if ($externalId) {
                $invoiceLine = InvoiceLine::with([
                    'tasks',
                    'invoice',
                    'product'
                ])->where('external_id', $externalId)->first();

                if (!$invoiceLine) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Invoice line not found with given external ID'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $invoiceLine,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            $invoiceLines = InvoiceLine::with([
                'tasks',
                'invoice',
                'product'
            ])->paginate(15);

            $responseData = [
                'invoice_lines' => $invoiceLines->items(),
                'pagination' => [
                    'total' => $invoiceLines->total(),
                    'per_page' => $invoiceLines->perPage(),
                    'current_page' => $invoiceLines->currentPage(),
                    'last_page' => $invoiceLines->lastPage(),
                    'from' => $invoiceLines->firstItem(),
                    'to' => $invoiceLines->lastItem()
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
                    'message' => 'An error occurred while fetching invoice line data',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}