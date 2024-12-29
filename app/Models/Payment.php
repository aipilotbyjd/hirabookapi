<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'amount', 'category', 'description', 'source', 'date', 'is_active'];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }
}
