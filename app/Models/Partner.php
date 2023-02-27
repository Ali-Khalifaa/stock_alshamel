<?php

namespace App\Models;

use App\Http\Resources\WorkflowTree\WorkflowTreeResource1;
use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Contracts\Activity;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Partner extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use LogTrait, HasApiTokens;


    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'name_e',
        'is_active',
        'email',
        'password',
        'mobile_no',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $user =  auth()->user()->id ?? "system";

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Partner')
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName} by ($user)");
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function hasChildren(){
        if ($this->companies ()->count () > 0){
            return true;
        }
        return false;
    }

    public function scopeFilter($query, $request)
    {
        return $query->where(function ($q) use ($request) {
            if ($request->search) {
                $q->where('name', 'like', '%' . $request->search . '%');
                $q->orWhere('name_e', 'like', '%' . $request->search . '%');
                $q->orWhere('mobile_no', 'like', '%' . $request->search . '%');
            }

            if ($request->name) {
                $q->orWhere('name', 'like', '%' . $request->name . '%');
            }

            if ($request->name_e) {
                $q->orWhere('name_e', 'like', '%' . $request->name_e . '%');
            }

            if ($request->email) {
                $q->orWhere('email', 'like', '%' . $request->email . '%');
            }

            if ($request->mobile_no) {
                $q->orWhere('mobile_no', 'like', '%' . $request->moblie_no . '%');
            }

            if ($request->is_active) {
                $q->where('is_active', $request->is_active);
            }
        });
    }

    public function  everything_about_the_company(){
        $company = $this->company;
        if ($company){
            $wf = WorkflowTree::query ()->where ('is_active',1)->where ('company_id',$company->id)->get();
            $company->work_flow_trees = WorkflowTreeResource1::collection ($wf);
            return $company;
        }
        return [];
    }
}
