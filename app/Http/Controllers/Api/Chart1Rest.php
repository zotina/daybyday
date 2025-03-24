<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\JsonResponseService;
use App\Models\Chart1Model;
use Illuminate\Http\Request;
class Chart1Rest extends Controller {

	protected $jsonResponseService;

	public function __construct(JsonResponseService $jsonResponseService) {
		$this->jsonResponseService = $jsonResponseService;
	}

	public function chart1() {
		try {
			$chart1 = Chart1Model::getAllChart1();
			return $this->jsonResponseService->createJsonResponse($chart1);
		} catch (\Exception $e) {
			return $this->jsonResponseService->createJsonResponse(
				[],
				'error',
				[['code' => 500, 'message' => $e->getMessage()]]
			);
		}
	}

}
