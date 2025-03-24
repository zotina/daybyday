<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMonth;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use App\Models\TaskStatus;
use App\Models\InvoiceStatus;

class ChartRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    public function paymentSummaryByMonth(Request $request)
    {
        try {
            $year = $request->query('year', date('Y')); 
            $paymentMonths = PaymentMonth::where('payment_month', 'like', "$year%")
                ->get()
                ->map(function ($item) {
                    return [
                        'payment_month' => $item->payment_month,
                        'amount_total' => $item->amountTotal
                    ];
                })->toArray();

            return $this->jsonResponseService->createJsonResponse(
                $paymentMonths,
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
                ['message' => 'Error fetching payment summary: ' . $e->getMessage()],
                null,
                null,
                ['status' => 500]
            );
        }
    }
    public function taskStatusSummary(Request $request)
    {
        try {
            $taskStatuses = TaskStatus::all()
                ->map(function ($item) {
                    return [
                        'status_name' => $item->{'Nom du Statut'},
                        'task_count' => $item->{'Nombre de Tâches'},
                        'percentage' => $item->Pourcentage
                    ];
                })->toArray();

            return $this->jsonResponseService->createJsonResponse(
                $taskStatuses,
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
                ['message' => 'Error fetching task status summary: ' . $e->getMessage()],
                null,
                null,
                ['status' => 500]
            );
        }
    }
    
    public function invoiceStatusSummary(Request $request)
    {
        try {
            $invoiceStatuses = InvoiceStatus::all()
                ->map(function ($item) {
                    return [
                        'status_name' => $item->{'Nom du Statut'},
                        'invoice_count' => $item->{'Nombre de Factures'},
                        'percentage' => $item->Pourcentage
                    ];
                })->toArray();

            return $this->jsonResponseService->createJsonResponse(
                $invoiceStatuses,
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
                ['message' => 'Error fetching invoice status summary: ' . $e->getMessage()],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}