<?php

namespace App\Models;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory, LogTrait;

    protected $fillable = [
        'name',
        'name_e',
        'parent_id',
        'is_active',
    ];
    protected $table = 'modules';
    protected $hidden = ['pivot'];

    protected $casts = [
        'is_active' => 'App\Enums\IsActive',
    ];

    protected $appends = ["haveChildren"];
    /**
     * this method used to make filter of query
     * @param Query  $query
     * @param $request
     * @return $query
     */
    public function scopeFilter($query, $request)
    {
        return $query->where(function ($q) use ($request) {
            if ($request->search) {
                $q->where('name', 'like', '%' . $request->search . '%');
                $q->orWhere('name_e', 'like', '%' . $request->search . '%');
            }

            if ($request->name) {
                $q->orWhere('name', 'like', '%' . $request->name . '%');
            }

            if ($request->name_e) {
                $q->orWhere('name_e', 'like', '%' . $request->name_e . '%');
            }

            if ($request->parent_id) {
                $q->orWhere('parent_id', $request->parent_id);
            }
        });
    }
    public function parent()
    {
        return $this->hasMany(Module::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->belongsTo(Module::class, 'parent_id', 'id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_module', 'module_id', 'company_id');
    }

    public function getHaveChildrenAttribute()
    {
        return static::where("parent_id", $this->id)->count() > 0;
    }
}
