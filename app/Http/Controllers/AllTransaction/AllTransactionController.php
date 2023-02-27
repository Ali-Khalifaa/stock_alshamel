<?php

namespace App\Http\Controllers\AllTransaction;

use App\Http\Controllers\Controller;
use App\Repositories\AllTransaction\AllTransactionRepository;
use App\Repositories\AllTransaction\AllTransactionInterface;
use App\Http\Resources\AllTransaction\AllTransactionResource;
use Illuminate\Http\Request;

class AllTransactionController extends Controller
{
    public function __construct(private AllTransactionInterface $modelInterface)
    {
    }
    public function  all(Request $request)
    {

        $models = $this->modelInterface->all($request);

        return responseJson(200, 'success', AllTransactionResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
}
