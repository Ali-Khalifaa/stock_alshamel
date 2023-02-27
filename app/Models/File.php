<?php

namespace App\Models;

use App\Traits\MediaTrait;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model implements HasMedia

{
    use HasFactory, MediaTrait, SoftDeletes;

}
