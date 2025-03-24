<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;

class SettingRest extends Controller
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    public function updateDiscount(Request $request)
    {
        try {
            $request->validate([
                'remise' => 'required|numeric|min:0|max:100', 
            ]);

            $setting = Setting::firstOrCreate(['id' => 1]); 
            $setting->update(['remise' => $request->input('remise')]);

            return $this->jsonResponseService->createJsonResponse(
                ['remise' => $setting->remise],
                'success',
                null,
                'Discount updated successfully',
                null,
                ['status' => 200]
            );
        } catch (\Exception $e) {
            return $this->jsonResponseService->createJsonResponse(
                null,
                'error',
                ['message' => 'Error updating discount: ' . $e->getMessage()],
                null,
                null,
                ['status' => 500]
            );
        }
    }
}