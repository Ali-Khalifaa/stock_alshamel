<?php

namespace App\Http\Controllers\Currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Http\Resources\Currency\CurrencyResource;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\CurrencyInterface;

class CurrencyController extends Controller
{

    public function __construct(private CurrencyInterface $modelInterface)
    {
    }
    public function  all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('currency');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('currency', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }
        return responseJson(200, 'success', CurrencyResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
}
