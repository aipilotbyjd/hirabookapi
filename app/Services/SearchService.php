<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Work;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SearchService
{
    protected $perPage = 10;

    public function searchPayments(array $params)
    {
        try {
            $query = Payment::query()
                ->where('user_id', Auth::id())
                ->where(function ($q) use ($params) {
                    $searchTerm = addcslashes($params['query'], '%_');
                    $q->where('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('from', 'LIKE', "%{$searchTerm}%");
                });

            // Apply date filter
            if (!empty($params['filter']) && $params['filter'] !== 'all') {
                $query = $this->applyDateFilter($query, $params['filter']);
            }

            // Apply amount filter
            if (!empty($params['minAmount']) || !empty($params['maxAmount'])) {
                $query = $this->applyAmountFilter($query, $params);
            }

            // Apply sorting
            $sortBy = $params['sortBy'] ?? 'date';
            $sortDirection = $params['sortDirection'] ?? 'desc';
            $query = $this->applySorting($query, $sortBy, $sortDirection);

            // Get paginated results
            $page = $params['page'] ?? 1;
            $payments = $query->paginate($this->perPage, ['*'], 'page', $page);

            return [
                'payments' => $payments->items(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
                'per_page' => $payments->perPage()
            ];

        } catch (\Exception $e) {
            Log::error('Error in searchPayments: ' . $e->getMessage());
            throw $e;
        }
    }

    public function searchAll(string $query)
    {
        try {
            $searchTerm = addcslashes($query, '%_');
            $userId = Auth::id();

            $payments = Payment::where('user_id', $userId)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('from', 'LIKE', "%{$searchTerm}%");
                })
                ->latest()
                ->limit(5)
                ->get();

            $works = Work::where('user_id', $userId)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                })
                ->latest()
                ->limit(5)
                ->get();

            return [
                'payments' => $payments,
                'works' => $works
            ];

        } catch (\Exception $e) {
            Log::error('Error in searchAll: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function applyDateFilter($query, $filter)
    {
        $today = now()->startOfDay();

        switch ($filter) {
            case 'today':
                return $query->whereDate('date', $today);
            case 'week':
                return $query->whereBetween('date', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek()
                ]);
            case 'month':
                return $query->whereBetween('date', [
                    $today->copy()->startOfMonth(),
                    $today->copy()->endOfMonth()
                ]);
            default:
                return $query;
        }
    }

    protected function applyAmountFilter($query, $params)
    {
        if (!empty($params['minAmount'])) {
            $query->where('amount', '>=', $params['minAmount']);
        }

        if (!empty($params['maxAmount'])) {
            $query->where('amount', '<=', $params['maxAmount']);
        }

        return $query;
    }

    protected function applySorting($query, $sortBy, $sortDirection)
    {
        $validSortFields = ['date', 'amount', 'from'];
        $validDirections = ['asc', 'desc'];

        if (in_array($sortBy, $validSortFields) && in_array($sortDirection, $validDirections)) {
            return $query->orderBy($sortBy, $sortDirection);
        }

        return $query->orderBy('date', 'desc');
    }
}
