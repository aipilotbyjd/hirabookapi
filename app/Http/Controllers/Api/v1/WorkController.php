<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Work;
use App\Http\Requests\WorkRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class WorkController extends BaseController
{
    /**
     * Display a listing of the works.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $user = Auth::user();
            $query = Work::query()
                ->with(['workItems'])
                ->where('user_id', $user->id);

            $filter = $request->input('filter', 'all');

            if ($request->has('is_active')) {
                $query->where('is_active', $request->input('is_active'));
            }

            switch ($filter) {
                case 'today':
                    $query->where('date', date('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('date', [date('Y-m-d', strtotime('last Monday')), date('Y-m-d', strtotime('next Sunday'))]);
                    break;
                case 'month':
                    $query->whereMonth('date', date('m'));
                    break;
                default:
                    break;
            }

            $works = $query->latest()->paginate($perPage);
            $total = Work::getTotalWorks($filter);

            return $this->sendResponse([
                'works' => $works,
                'total' => $total
            ], 'Works fetched successfully');
        } catch (\Exception $e) {
            logError('WorkController', 'index', $e->getMessage());
            return $this->sendError('Failed to fetch works', [], 500);
        }
    }

    /**
     * Store a newly created work.
     */
    public function store(WorkRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();
            $validated['user_id'] = $user->id;

            $work = Work::create($validated);
            $work->workItems()->createMany($request->entries);
            $work->total = $work->workItems->sum(function ($item) {
                return $item->price * $item->diamond;
            });
            $work->save();

            return $this->sendResponse($work->load(['workItems']), 'Work created successfully');
        } catch (\Exception $e) {
            logError('WorkController', 'store', $e->getMessage());
            return $this->sendError('Failed to create work', [], 500);
        }
    }

    /**
     * Display the specified work.
     */
    public function details($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $work = Work::with(['workItems'])
                ->where('user_id', $user->id)
                ->findOrFail($id);

            return $this->sendResponse($work, 'Work fetched successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Work not found', [], 404);
        } catch (\Exception $e) {
            logError('WorkController', 'details', $e->getMessage());
            return $this->sendError('Failed to fetch work', [], 500);
        }
    }

    /**
     * Update the specified work.
     */
    public function update(WorkRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $work = Work::where('user_id', $user->id)->findOrFail($id);

            $validated = $request->validated();
            $work->update($validated);
            $work->workItems()->delete();
            $work->workItems()->createMany($request->entries);
            $work->total = $work->workItems->sum(function ($item) {
                return $item->price * $item->diamond;
            });
            $work->save();

            return $this->sendResponse($work->load(['workItems']), 'Work updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Work not found', [], 404);
        } catch (\Exception $e) {
            logError('WorkController', 'update', $e->getMessage());
            return $this->sendError('Failed to update work', [], 500);
        }
    }

    /**
     * Remove the specified work.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $work = Work::where('user_id', $user->id)->findOrFail($id);
            $work->workItems()->delete();
            $work->delete();

            return $this->sendResponse([], 'Work deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Work not found', [], 404);
        } catch (\Exception $e) {
            logError('WorkController', 'destroy', $e->getMessage());
            return $this->sendError('Failed to delete work', [], 500);
        }
    }
}
