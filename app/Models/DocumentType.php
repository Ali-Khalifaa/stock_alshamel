<?php

namespace App\Models;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'name',
        'name_e',
        'is_default',
    ];

    public function screens()
    {
        return $this->belongsToMany(Screen::class, 'screen_document_types', 'documentType_id', 'screen_id', 'id', 'id');
    }

}
