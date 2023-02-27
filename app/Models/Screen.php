<?php

namespace App\Models;

use App\Traits\LogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screen extends Model
{
    use SoftDeletes, LogTrait;

    protected $table = 'screens';
    protected $fillable = [
        'name',
        'name_e',
        'title',
        'title_e',
        'serial_id',
    ];

    public function helpfiles()
    {
        return $this->belongsToMany(Helpfile::class, 'screens_helpfiles', 'screen_id', 'helpfile_id');
    }

    public function buttons()
    {
        return $this->belongsToMany(Button::class, 'screens_buttons', 'screen_id', 'button_id');
    }

    public function serial()
    {
        return $this->belongsTo(Serial::class);
    }

    public function documentTypes()
    {
        return $this->belongsToMany(DocumentType::class, 'screen_document_types');
    }
}
