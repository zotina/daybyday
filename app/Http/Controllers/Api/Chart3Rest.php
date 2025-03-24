<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\JsonResponseService;
use App\Models\Chart3Model;
use Illuminate\Http\Request;
class Chart3Rest extends Controller {
	protected $jsonResponseService;

	public function __construct(JsonResponseService $jsonResponseService) {
		$this->jsonResponseService = $jsonResponseService;
	}

	public function chart3() {
		try {
			$chart3 = Chart3Model::getAllChart3();
			return $this->jsonResponseService->createJsonResponse($chart3);
		} catch (\Exception $e) {
			return $this->jsonResponseService->createJsonResponse(
				[],
				'error',
				[['code' => 500, 'message' => $e->getMessage()]]
			);
		}
	}
}
