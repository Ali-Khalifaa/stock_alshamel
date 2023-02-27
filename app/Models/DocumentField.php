<?php

namespace App\Models;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentField extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'name',
        'name_e',
        'type',
        'is_reference',
    ];

    /**
     * Get all of the comments for the DocumentField
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function archiveClosedReference()
    {
        return $this->hasMany(ArchiveClosedReference::class, 'arch_docfields_id', 'id');
    }
}
