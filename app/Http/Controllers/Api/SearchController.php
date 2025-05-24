<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchResource;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'page' => 'integer|min:1',
            'filter' => 'string|in:all,today,week,month',
            'sortBy' => 'string|in:date,amount,from',
            'sortDirection' => 'string|in:asc,desc',
            'minAmount' => 'numeric|nullable',
            'maxAmount' => 'numeric|nullable|gte:minAmount'
        ]);

        $payments = $this->searchService->searchPayments($request->all());

        return response()->json([
            'status' => 'success',
            'data' => [
                'payments' => SearchResource::collection($payments->items()),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
            ]
        ]);
    }

    public function searchAll(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'page' => 'integer|min:1'
        ]);

        $results = $this->searchService->searchAll($request->input('query'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'payments' => SearchResource::collection($results['payments']),
                'works' => $results['works'],
                'total_payments' => $results['total_payments'],
                'total_works' => $results['total_works'],
            ]
        ]);
    }
}
