<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchResource;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:1',
                'page' => 'nullable|integer|min:1',
                'filter' => 'nullable|string|in:all,today,week,month',
                'sortBy' => 'nullable|string|in:date,amount,from',
                'sortDirection' => 'nullable|string|in:asc,desc',
                'minAmount' => 'nullable|numeric|min:0',
                'maxAmount' => 'nullable|numeric|gt:minAmount'
            ]);

            $results = $this->searchService->searchPayments($validated);
            $payments = SearchResource::collection($results['payments']);

            return response()->json([
                'status' => 'success',
                'message' => 'Search results retrieved successfully',
                'data' => [
                    'payments' => $payments,
                    'pagination' => [
                        'current_page' => $results['current_page'],
                        'last_page' => $results['last_page'],
                        'total' => $results['total'],
                        'per_page' => $results['per_page'],
                        'has_more_pages' => $results['current_page'] < $results['last_page']
                    ]
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while searching',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:1',
                'page' => 'nullable|integer|min:1'
            ]);

            $results = $this->searchService->searchAll([
                'query' => $validated['query'],
                'page' => $validated['page'] ?? 1
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Global search results retrieved successfully',
                'data' => [
                    'payments' => SearchResource::collection($results['payments']),
                    'works' => SearchResource::collection($results['works']),
                    'pagination' => $results['pagination']
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Global search error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while performing global search',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
