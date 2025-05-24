<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function searchPayments(array $params)
    {
        try {
            $query = $params['query'];
            $page = $params['page'] ?? 1;
            $filter = $params['filter'] ?? 'all';
            $sortBy = $params['sortBy'] ?? 'date';
            $sortDirection = $params['sortDirection'] ?? 'desc';
            $minAmount = isset($params['minAmount']) ? (float) $params['minAmount'] : null;
            $maxAmount = isset($params['maxAmount']) ? (float) $params['maxAmount'] : null;

            $paymentsQuery = Payment::query()
                ->where('user_id', Auth::id())
                ->where(function (Builder $q) use ($query) {
                    $q->where('from', 'like', '%' . addcslashes($query, '%_') . '%')
                        ->orWhere('description', 'like', '%' . addcslashes($query, '%_') . '%');
                });

            $paymentsQuery = $this->applyDateFilter($paymentsQuery, $filter);
            $paymentsQuery = $this->applyAmountFilter($paymentsQuery, $minAmount, $maxAmount);
            $paymentsQuery = $this->applySorting($paymentsQuery, $sortBy, $sortDirection);

            return $paymentsQuery->with(['source' => function($q) {
                $q->select('id', 'name', 'icon');
            }])->paginate(10, [
                'id',
                'from',
                'description',
                'amount',
                'date',
                'source_id',
                'created_at',
                'updated_at'
            ], 'page', $page);

        } catch (\Exception $e) {
            \Log::error('Search payments error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function searchAll(string $query)
    {
        try {
            $userId = Auth::id();
            $searchQuery = addcslashes($query, '%_');

            $payments = Payment::where('user_id', $userId)
                ->where(function (Builder $q) use ($searchQuery) {
                    $q->where('from', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                })
                ->with(['source' => function($q) {
                    $q->select('id', 'name', 'icon');
                }])
                ->select([
                    'id',
                    'from',
                    'description',
                    'amount',
                    'date',
                    'source_id',
                    'created_at',
                    'updated_at'
                ])
                ->latest('date')
                ->take(5)
                ->get();

            $works = Work::where('user_id', $userId)
                ->where(function (Builder $q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                })
                ->select([
                    'id',
                    'name',
                    'description',
                    'date',
                    'created_at',
                    'updated_at'
                ])
                ->latest('date')
                ->take(5)
                ->get();

            return [
                'payments' => $payments,
                'works' => $works,
                'total_payments' => Payment::where('user_id', $userId)
                    ->where(function (Builder $q) use ($searchQuery) {
                        $q->where('from', 'like', "%{$searchQuery}%")
                            ->orWhere('description', 'like', "%{$searchQuery}%");
                    })
                    ->count(),
                'total_works' => Work::where('user_id', $userId)
                    ->where(function (Builder $q) use ($searchQuery) {
                        $q->where('name', 'like', "%{$searchQuery}%")
                            ->orWhere('description', 'like', "%{$searchQuery}%");
                    })
                    ->count(),
            ];

        } catch (\Exception $e) {
            \Log::error('Search all error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function applyDateFilter(Builder $query, string $filter): Builder
    {
        switch ($filter) {
            case 'today':
                return $query->whereDate('date', today());
            case 'week':
                return $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
            case 'month':
                return $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            default:
                return $query;
        }
    }

    private function applyAmountFilter(Builder $query, ?float $minAmount, ?float $maxAmount): Builder
    {
        if ($minAmount !== null && $minAmount >= 0) {
            $query->where('amount', '>=', $minAmount);
        }
        if ($maxAmount !== null && $maxAmount >= 0) {
            $query->where('amount', '<=', $maxAmount);
        }
        return $query;
    }

    private function applySorting(Builder $query, string $sortBy, string $sortDirection): Builder
    {
        $validFields = ['date', 'amount', 'from'];
        $validDirections = ['asc', 'desc'];

        if (in_array($sortBy, $validFields) && in_array(strtolower($sortDirection), $validDirections)) {
            return $query->orderBy($sortBy, $sortDirection);
        }

        return $query->orderBy('date', 'desc'); // Default sorting
    }
}
