<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\JsonResponseService;
use App\Models\Chart2Model;
use Illuminate\Http\Request;
class Chart2Rest extends Controller {
	protected $jsonResponseService;

	public function __construct(JsonResponseService $jsonResponseService) {
		$this->jsonResponseService = $jsonResponseService;
	}

	public function chart2() {
		try {
			$chart2 = Chart2Model::getAllChart2();
			return $this->jsonResponseService->createJsonResponse($chart2);
		} catch (\Exception $e) {
			return $this->jsonResponseService->createJsonResponse(
				[],
				'error',
				[['code' => 500, 'message' => $e->getMessage()]]
			);
		}
	}
}
