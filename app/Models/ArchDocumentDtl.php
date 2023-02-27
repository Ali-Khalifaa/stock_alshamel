<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogTrait;
use App\Models\GenArchDocType;
use App\Models\DocumentField;

class ArchDocumentDtl extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'gen_arch_doc_type_id',
        'arch_doc_field_id',
        'field_value',
    ];

    // relation
    public function genArchDocType()
    {
        return $this->belongsTo(GenArchDocType::class, 'gen_arch_doc_type_id', 'id');
    }

    public function archDocType()
    {
        return $this->belongsTo(DocumentField::class, 'arch_doc_field_id', 'id');
    }
}
