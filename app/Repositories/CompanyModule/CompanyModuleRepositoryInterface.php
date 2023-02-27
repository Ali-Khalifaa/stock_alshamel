<?php

namespace App\Repositories\CompanyModule;

interface CompanyModuleRepositoryInterface
{

    public function getCompanyModules($request);

    public function find($id);

    public function create($request);

    public function update($request, $id);

    public function logs($id);

    public function delete($id);




}
