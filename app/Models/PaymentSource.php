<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSource extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'name_en', 'name_gu', 'name_hi', 'icon', 'is_active'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', '1');
    }
}
