<?php

namespace App\Http\Controllers\WorkflowTree;

use App\Http\Controllers\ResponseController;
use App\Http\Requests\WorkflowTree\StoreWorkflowTreeRequest;
use App\Http\Requests\WorkflowTree\UpdateWorkflowTreeRequest;
use App\Http\Resources\WorkflowTree\WorkflowTreeResource1;
use App\Http\Resources\WorkflowTree\WorkflowTreeResource;
use App\Models\Company;
use App\Models\WorkflowTree;
use App\Repositories\WorkflowTree\WorkflowTreeRepositoryInterface;
use Illuminate\Http\Request;

class WorkflowTreeController extends ResponseController
{

    protected $repository;
    protected $resource = WorkflowTreeResource::class;

    public function __construct(WorkflowTreeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('work_flow_trees');
            if (!$models) {
                $models = $this->repository->all($request);
                cachePut('work_flow_trees', $models);
            }
        } else {
            $models = $this->repository->all($request);
        }
        return responseJson(200, 'success', ($this->resource)::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function find($id)
    {
        $model = cacheGet('work_flow_trees_' . $id);
        if (!$model) {
            $model = $this->repository->find($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            } else {
                cachePut('work_flow_trees_' . $id, $model);
            }
        }
        return responseJson(200, __('Done'), new WorkflowTreeResource($model), );
    }
    public function getRootNodes(){
        return $this->repository->getRootNodes();
    }
    public function getChildNodes($parentId){
        return $this->repository->getChildNodes($parentId);
    }

    public function everything_about_the_company($id)
    {
        $company = Company::query()->find($id);
        if (!$company) {
            return responseJson(404, __('message.data not found'));
        }
        $wf = WorkflowTree::query()->where('is_active', 1)->where('company_id', $company->id)->get();
        $company->work_flow_trees = WorkflowTreeResource1::collection($wf);
        return responseJson(200, __(''), $company);
    }

    public function store(StoreWorkflowTreeRequest $request)
    {
        $model = $this->repository->create($request);
        return responseJson(200, 'success', new WorkflowTreeResource($model));
    }

    public function update(UpdateWorkflowTreeRequest $request, $id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $this->repository->update($request, $id);
        $model->refresh();
        return responseJson(200, 'success', new WorkflowTreeResource($model));
    }

    public function delete($id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $this->repository->delete($id);
        return responseJson(200, __('Done'));
    }

    public function bulkDelete(Request $request){
        foreach ($request->ids as $id){
            $this->repository->delete($id);
        }
        return  responseJson(200, __('Done'));
    }

    public function logs($id)
    {
        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }

        $logs = $this->repository->logs($id);
        return responseJson(200, 'success', \App\Http\Resources\Log\LogResource::collection($logs));
    }
}
