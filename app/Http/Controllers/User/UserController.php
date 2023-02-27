<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ResponseController;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Resources\User\UserResource;
use App\Http\Request\StoreUserRequest;
use App\Http\Request\UpdateUserRequest;

class UserController extends ResponseController
{
    public function __construct(UserRepositoryInterface $userRepository){}

    public function index(Request $request)
    {
        //// Example Success
        return $this->successResponse(UserResource::collection($this->userRepository->getAllUsers()), 'Done', 200);

        ///Example Error 
        //return $this->errorResponse('', 422);
    }

    public function store(StoreUserRequest  $request)
    {

    }

    public function update(UpdateUserRequest  $request)
    {

    }
   
}
