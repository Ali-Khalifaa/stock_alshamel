<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;


class CheckIfValidTokenController extends Controller
{

    public function checkIsValidToken()
    {
        return responseJson(200,'Valid Token');
    }

}
