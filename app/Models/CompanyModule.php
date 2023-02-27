<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanyModule extends Model
{
    use LogsActivity;
    use CausesActivity;

    protected $table = 'company_modules';
    protected $fillable = [
        'company_id',
        'module_id',
        'start_date',
        'end_date',
        'allowed_users_no',
    ];

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->causer_id = auth()->user()->id ?? 0;
        $activity->causer_type = auth()->user()->role ?? "admin";
    }

    public function getActivitylogOptions(): LogOptions
    {
        $user = auth()->user()->id ?? "system";

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Partner')
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName} by ($user)");
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function scopeFilter($query, $request)
    {
        return $query->where(function ($q) use ($request) {
            if ($request->search) {
                $q->where('allowed_users_no', 'like', '%' . $request->search . '%');
            }

            if ($request->start_date && $request->end_date) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date]);
            }

            if ($request->start_date && !$request->end_date) {
                $q->where('start_date', '>=', $request->start_date);
            }

            if (!$request->start_date && $request->end_date) {
                $q->where('start_date', '<=', $request->end_date);
            }

            if ($request->company_id) {
                $q->where('company_id', $request->company_id);
            }

            if ($request->module_id) {
                $q->where('module_id', $request->module_id);
            }
        });
    }
}
