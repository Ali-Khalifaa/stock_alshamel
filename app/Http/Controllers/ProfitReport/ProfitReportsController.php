<?php

namespace App\Http\Controllers\ProfitReport;

use App\Http\Controllers\Controller;
use App\Repositories\ProfitReport\ProfitReportInterface;
use App\Http\Resources\ProfitReport\ProfitReportsResource;
use Illuminate\Http\Request;

class ProfitReportsController extends Controller
{
    public function __construct(private ProfitReportInterface $modelInterface)
    {
    }
    public function  all(Request $request)
    {
        $models = $this->modelInterface->all($request);
        return responseJson(200, 'success', ProfitReportsResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
}
