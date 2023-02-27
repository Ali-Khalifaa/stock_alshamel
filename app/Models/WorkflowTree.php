<?php

namespace App\Models;

use App\Traits\LogTrait;
use App\Traits\MediaTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowTree extends Model implements \Spatie\MediaLibrary\HasMedia
{
    use SoftDeletes, LogTrait, MediaTrait;
    protected $appends = ["haveChildren"];
    protected $table = 'workflow_trees';
    protected $fillable = [
        'name',
        'name_e',
        'is_active',
        'parent_id',
        'partner_id',
        'company_id',
        'module_id',
        'screen_id',
        'id_sort',
    ];
    /**
     * return child of this parent
     */
    public function child()
    {
        return $this->belongsTo(WorkflowTree::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(WorkflowTree::class, 'parent_id', 'id');
    }

    public function getHaveChildrenAttribute()
    {
        return static::where("parent_id", $this->id)->count() > 0;
    }
    /**
     * return relation  with  partner
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    /**
     * return relation with  company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * return relation with  module
     */
    public function moduleName()
    {
        return $this->belongsTo(Module::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * return relation with  screen
     */
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }


    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        $user = @auth()->user()->id ?: "system";

        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('Workflow Tree')
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName} by ($user)");
    }

}
