<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogTrait;

class Currency extends Model
{
    use HasFactory , LogTrait;
     protected $table = 'general_currencies';

}
