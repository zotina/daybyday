<?php
namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Offer;
use App\Models\Invoice;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Invoice\InvoiceService;

class PagesApiController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $invoiceService = new InvoiceService();
        
        
        $today = today();
        $startDateCurrentMonth = today()->subDays(30);
        $periodCurrentMonth = CarbonPeriod::create($startDateCurrentMonth, $today);
        $datasheetCurrentMonth = [];
        
        
        foreach ($periodCurrentMonth as $date) {
            $formattedDate = $date->format('Y-m-d');
            $datasheetCurrentMonth[$formattedDate] = [
                "tasks" => 0
            ];
        }
        
        
        $tasksCurrentMonth = Task::whereBetween('created_at', [$startDateCurrentMonth, now()])->get();
        foreach ($tasksCurrentMonth as $task) {
            $dateKey = $task->created_at->format('Y-m-d');
            if (isset($datasheetCurrentMonth[$dateKey])) {
                $datasheetCurrentMonth[$dateKey]["tasks"]++;
            }
        }
        
        
        $currentMonthData = [
            'payment' => $invoiceService->getTotalAmountDue(),
            'invoices' => Invoice::count(),
            'offer' => Offer::count(),
            'total_tasks' => Task::count(),
            'total_projects' => Project::count(),
            'total_clients' => Client::count(),
        ];
        
        
        $endDatePreviousMonth = today()->subDays(30);
        $startDatePreviousMonth = today()->subDays(60);
        
        
        
        
        $previousInvoiceService = new InvoiceService();
        
        
        $previousMonthInvoices = Invoice::whereDate('created_at', '>=', $startDatePreviousMonth)
                                        ->whereDate('created_at', '<=', $endDatePreviousMonth)
                                        ->count();
        $previousMonthOffers = Offer::whereDate('created_at', '>=', $startDatePreviousMonth)
                                    ->whereDate('created_at', '<=', $endDatePreviousMonth)
                                    ->count();
        $previousMonthTasks = Task::whereDate('created_at', '>=', $startDatePreviousMonth)
                                  ->whereDate('created_at', '<=', $endDatePreviousMonth)
                                  ->count();
        $previousMonthProjects = Project::whereDate('created_at', '>=', $startDatePreviousMonth)
                                        ->whereDate('created_at', '<=', $endDatePreviousMonth)
                                        ->count();
        $previousMonthClients = Client::whereDate('created_at', '>=', $startDatePreviousMonth)
                                      ->whereDate('created_at', '<=', $endDatePreviousMonth)
                                      ->count();
        
        
        
        $previousMonthPayment = $previousInvoiceService->getTotalAmountDue(); 
        
        $previousMonthData = [
            'payment' => $previousMonthPayment,
            'invoices' => $previousMonthInvoices,
            'offer' => $previousMonthOffers,
            'total_tasks' => $previousMonthTasks,
            'total_projects' => $previousMonthProjects,
            'total_clients' => $previousMonthClients,
        ];
        
        
        $percentageDifferences = [];
        foreach ($currentMonthData as $key => $currentValue) {
            $previousValue = $previousMonthData[$key];
            
            if ($previousValue > 0) {
                $percentageDiff = (($currentValue - $previousValue) / $previousValue) * 100;
                $percentageDifferences[$key] = round($percentageDiff, 2);
            } else {
                
                $percentageDifferences[$key] = $currentValue > 0 ? 100 : 0;
            }
        }
        
        $responseData = [
            
            'payment' => $currentMonthData['payment'],
            'invoices' => $currentMonthData['invoices'],
            'offer' => $currentMonthData['offer'],
            'total_tasks' => $currentMonthData['total_tasks'],
            'total_projects' => $currentMonthData['total_projects'],
            'total_clients' => $currentMonthData['total_clients'],
            'datasheet' => $datasheetCurrentMonth,
            
            'payment_percentage' => $percentageDifferences['payment'],
            'invoices_percentage' => $percentageDifferences['invoices'],
            'offer_percentage' => $percentageDifferences['offer'],
            'total_tasks_percentage' => $percentageDifferences['total_tasks'],
            'total_projects_percentage' => $percentageDifferences['total_projects'],
            'total_clients_percentage' => $percentageDifferences['total_clients'],
        ];
        
        return response()->json($responseData, 200);
    }
}