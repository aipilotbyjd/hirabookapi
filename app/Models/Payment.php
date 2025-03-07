<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'amount', 'category', 'description', 'source_id', 'date', 'is_active', 'user_id', 'from'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->belongsTo(PaymentSource::class);
    }

    public static function getTotalPayments($filter)
    {
        $user = Auth::user();
        $query = self::where('is_active', '1')->where('user_id', $user->id);

        switch ($filter) {
            case 'today':
                $query->where('date', date('Y-m-d'));
                break;
            case 'week':
                $query->whereBetween('date', [date('Y-m-d', strtotime('last Monday')), date('Y-m-d', strtotime('next Sunday'))]);
                break;
            case 'month':
                $query->whereMonth('date', date('m'))->whereYear('date', date('Y'));
                break;
            default:
                break;
        }

        return $query->sum('amount');
    }
}
