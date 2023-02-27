<?php

namespace App\Models;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScreenButton extends Model
{
    use SoftDeletes, LogTrait;

    protected $table = 'screens_buttons';

    protected $guarded = ["id"];

    public function screens()
    {
        return Screen::query()->whereHas('buttons')->with('buttons')->get();
    }

}
