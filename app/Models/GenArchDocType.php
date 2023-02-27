<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogTrait;

class GenArchDocType extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'name',
        'name_e',
        'parent_id',
        'is_valid',
    ];

    // relations
    public function parent()
    {
        return $this->belongsTo(GenArchDocType::class,'parent_id','id');
    }
}
