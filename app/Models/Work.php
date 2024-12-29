<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'date', 'is_active'];

    public function work_items()
    {
        return $this->hasMany(WorkItem::class);
    }
}
