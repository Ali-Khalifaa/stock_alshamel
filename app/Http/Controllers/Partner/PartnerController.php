<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\Log\LogResource;
use App\Http\Resources\Partner\PartnerResource;
use App\Repositories\Partner\PartnerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{

    protected $repository;
    protected $resource = PartnerResource::class;

    public function __construct(PartnerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('Partners');

            if (!$models) {
                $models = $this->repository->getAllPartners($request);

                cachePut('Partners', $models);
            }
        } else {

            $models = $this->repository->getAllPartners($request);
        }

        return responseJson(200, 'success', ($this->resource)::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function find($id)
    {

        $model = cacheGet('Partners_' . $id);

        if (!$model) {
            $model = $this->repository->find($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            } else {
                cachePut('Partners_' . $id, $model);
            }
        }
        return responseJson(200, __('Done'), new PartnerResource($model), );

    }

    public function store(StorePartnerRequest $request)
    {

        return responseJson(200, __('Done'), $this->repository->create($request->validated()));

    }

    public function update(UpdatePartnerRequest $request, $id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $model = $this->repository->update($request->validated(), $id);

        return responseJson(200, __('Done'));

    }

    public function delete($id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        if ($model->companies()->count() > 0) {
            return responseJson(400, __('message.data has relation cant delete'));
        }
        $this->repository->delete($id);
        return responseJson(200, __('Done'));

    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $model = $this->repository->find($id);
            $arr = [];
            if ($model->hasChildren()) {
                $arr[] = $id;
                continue;
            }
            $this->repository->delete($id);
        }
        if (count($arr) > 0) {
            return responseJson(200, __('some items has relation cant delete'));
        }
        return responseJson(200, __('Done'));
    }

    public function logs($id)
    {
        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }

        $logs = $this->repository->logs($id);
        return responseJson(200, 'success', LogResource::collection($logs));
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::guard('partner')->attempt($request->only("email", "password"))) {
            return responseJson(422, 'Email Or Password is wrong!');
        }
        $authUser = new PartnerResource(Auth::guard('partner')->user());
        $success['token'] = $authUser->createToken('sanctumPartner')->plainTextToken;
        $success['partner'] = $authUser;
        $success['companies'] = $authUser->everything_about_the_company();
        return responseJson(200, 'Login Successfully', $success);
    }
}
