<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;

class LeadRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * Retrieve lead data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeadData(Request $request)
    {
        try {
            $leadId = $request->query('id');
            $externalId = $request->query('external_id');

            if ($leadId) {
                $lead = Lead::with([
                    'user',
                    'creator',
                    'client',
                    'comments',
                    'activity',
                    'appointments',
                    'status',
                    'invoice',
                    'offers'
                ])->find($leadId);

                if (!$lead) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Lead not found'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                // Envelopper le lead dans un tableau "leads" avec pagination
                $responseData = [
                    'leads' => [$lead],
                    'pagination' => [
                        'total' => 1,
                        'per_page' => 1,
                        'current_page' => 1,
                        'last_page' => 1,
                        'from' => 1,
                        'to' => 1
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
            }

            if ($externalId) {
                $lead = Lead::with([
                    'user',
                    'creator',
                    'client',
                    'comments',
                    'activity',
                    'appointments',
                    'status',
                    'invoice',
                    'offers'
                ])->where('external_id', $externalId)->first();

                if (!$lead) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Lead not found with given external ID'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                // Envelopper le lead dans un tableau "leads" avec pagination
                $responseData = [
                    'leads' => [$lead],
                    'pagination' => [
                        'total' => 1,
                        'per_page' => 1,
                        'current_page' => 1,
                        'last_page' => 1,
                        'from' => 1,
                        'to' => 1
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
            }

            // Cas par défaut : pagination
            $leads = Lead::with([
                'user',
                'creator',
                'client',
                'comments',
                'activity',
                'appointments',
                'status',
                'invoice',
                'offers'
            ])->paginate(15);

            $responseData = [
                'leads' => $leads->items(),
                'pagination' => [
                    'total' => $leads->total(),
                    'per_page' => $leads->perPage(),
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem()
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
                    'message' => 'An error occurred while fetching lead data',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}