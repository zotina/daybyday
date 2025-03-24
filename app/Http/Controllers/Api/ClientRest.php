<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientRest extends Controller
{
    public function __construct()
    {
        $this->middleware('client.update', ['only' => ['update']]);
        $this->middleware('is.demo', ['only' => ['destroy']]);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $clients = Client::select(['external_id', 'company_name', 'vat', 'address'])
                ->paginate($request->input('per_page', 10)); 
    
            return response()->json([
                'success' => true,
                'data' => $clients->items(),  // Données paginées
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'last_page' => $clients->lastPage(),
                    'next_page_url' => $clients->nextPageUrl(),
                    'prev_page_url' => $clients->previousPageUrl(),
                ],
                'message' => 'Clients retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving clients: ' . $e->getMessage()
            ], 500);
        }
    }
    
}