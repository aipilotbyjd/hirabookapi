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

    /**
     * Global search for payments and works with pagination
     * @param array $params ['query' => string, 'page' => int]
     */
    public function searchAll(array $params)
    {
        try {
            $searchTerm = addcslashes($params['query'], '%_');
            $userId = Auth::id();
            $page = $params['page'] ?? 1;

            // Paginate payments
            $paymentsQuery = Payment::where('user_id', $userId)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('description', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('from', 'LIKE', "%{$searchTerm}%");
                })
                ->latest();
            $payments = $paymentsQuery->paginate($this->perPage, ['*'], 'page', $page);

            // Paginate works
            $worksQuery = Work::where('user_id', $userId)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                })
                ->latest();
            $works = $worksQuery->paginate($this->perPage, ['*'], 'page', $page);

            return [
                'payments' => $payments->items(),
                'works' => $works->items(),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => max($payments->lastPage(), $works->lastPage()),
                    'total_payments' => $payments->total(),
                    'total_works' => $works->total(),
                    'per_page' => $this->perPage,
                    'has_more_pages' => ($payments->currentPage() < $payments->lastPage()) || ($works->currentPage() < $works->lastPage())
                ]
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
