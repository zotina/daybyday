<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;

class OfferRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * Retrieve offer data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOfferData(Request $request)
    {
        try {
            $offerId = $request->query('id');
            $externalId = $request->query('external_id');

            if ($offerId) {
                $offer = Offer::with([
                    'invoiceLines',
                    'invoice'
                ])->find($offerId);

                if (!$offer) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Offer not found'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $offer,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            if ($externalId) {
                $offer = Offer::with([
                    'invoiceLines',
                    'invoice'
                ])->where('external_id', $externalId)->first();

                if (!$offer) {
                    return $this->jsonResponseService->createJsonResponse(
                        null,
                        'error',
                        ['message' => 'Offer not found with given external ID'],
                        null,
                        null,
                        ['status' => 404]
                    );
                }

                return $this->jsonResponseService->createJsonResponse(
                    $offer,
                    'success',
                    null,
                    null,
                    null,
                    ['status' => 200]
                );
            }

            $offers = Offer::with([
                'invoiceLines',
                'invoice'
            ])->paginate(15);

            $responseData = [
                'offers' => $offers->items(),
                'pagination' => [
                    'total' => $offers->total(),
                    'per_page' => $offers->perPage(),
                    'current_page' => $offers->currentPage(),
                    'last_page' => $offers->lastPage(),
                    'from' => $offers->firstItem(),
                    'to' => $offers->lastItem()
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
                    'message' => 'An error occurred while fetching offer data',
                    'details' => $e->getMessage()
                ],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}