<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;


use App\Http\Resources\Admin\AdminResource;

class LoginController extends Controller
{

    public function login(LoginRequest $request)
    {
        if (!Auth::guard('admin')->attempt($request->only("email", "password"))) {
            return responseJson(422, 'Email Or Password is wrong!');
        }
        $authUser = new AdminResource(Auth::guard('admin')->user());
        $success['token'] = $authUser->createToken('sanctumAuth')->plainTextToken;
        $success['admin'] = $authUser;
        return responseJson(200, 'Login Successfully', $success);
    }
}
