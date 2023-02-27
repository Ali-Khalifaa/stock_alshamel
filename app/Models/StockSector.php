<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogTrait;

class StockSector extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'name',
        'name_e',
    ];
}
