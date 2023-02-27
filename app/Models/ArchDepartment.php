<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_e',
        'parent_id',
    ];

    /**
     * Get the arch_department that owns the ArchDepartment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ArchDepartment::class, 'parent_id', 'id');
    }
}
