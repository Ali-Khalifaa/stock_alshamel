<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveClosedReference extends Model
{
    use HasFactory;
    protected $fillable = [
        'arch_docfields_id',
        'field_value',
    ];

    /**
     * Get the user that owns the ArchiveClosedReference
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentField()
    {
        return $this->belongsTo(DocumentField::class, 'arch_docfields_id', 'id');
    }
}
