<?php

namespace App\Exceptions;


class NotFoundException extends \Exception
{
    public function render()
    {
        return responseJson(404, "Not Found");
    }
}
