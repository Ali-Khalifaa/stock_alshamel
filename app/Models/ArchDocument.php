<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'gen_arch_doc_type',
        'doc_description',
        'doc_status',
        'url_reference',
    ];

    /**
     * Get the genArchDocType that owns the ArchDocument
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function genArchDocType()
    {
        return $this->belongsTo(GenArchDocType::class, 'gen_arch_doc_type', 'id');
    }
    public function docStatus()
    {
        return $this->belongsTo(ArchDocStatus::class, 'doc_status', 'id');
    }
}
