<?php

namespace App\Http\Controllers\RealizedUnrealized;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\RealizedUnrealized\RealizedUnrealizedInterface;
use App\Repositories\RealizedUnrealized\RealizedUnrealizedRepository;
use App\Http\Resources\RealizedUnrealized\RealizedUnrealizedResource;

class RealizedUnrealizedController extends Controller
{
    public function __construct(private RealizedUnrealizedRepository $modelInterface)
    {
    }
    public function  all(Request $request)
    {

        $models = $this->modelInterface->all($request);
        return responseJson(200, 'success', RealizedUnrealizedResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
}
