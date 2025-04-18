<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Http\Controllers\BaseController;
use App\Models\PaymentSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    /**
     * Display a listing of payments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $user = Auth::user();
            $query = Payment::query()->with(['source'])->where('user_id', $user->id);
            $filter = $request->input('filter', 'all');

            if ($request->has('source_id')) {
                $query->where('source_id', $request->input('source_id'));
            }
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
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

            $payments = $query->latest()->paginate($perPage);
            $total = Payment::getTotalPayments($filter);

            return $this->sendResponse([
                'payments' => $payments,
                'total' => $total
            ], 'Payments retrieved successfully');
        } catch (\Exception $e) {
            logError('PaymentController', 'index', $e->getMessage());
            return $this->sendError('Error retrieving payments', [], 500);
        }
    }

    /**
     * Store a newly created payment.
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();
            $validated['date'] = isset($validated['date']) ? date('Y-m-d', strtotime($validated['date'])) : null;
            $validated['user_id'] = $user->id;
            $payment = Payment::create($validated);

            return $this->sendResponse($payment, 'Payment created successfully');
        } catch (\Exception $e) {
            logError('PaymentController', 'store', $e->getMessage());
            return $this->sendError('Error creating payment', [], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function details($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $payment = Payment::with(['source'])
                ->where('user_id', $user->id)
                ->findOrFail($id);

            return $this->sendResponse($payment, 'Payment details retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Payment not found', [], 404);
        } catch (\Exception $e) {
            logError('PaymentController', 'details', $e->getMessage());
            return $this->sendError('Error retrieving payment details', [], 500);
        }
    }

    /**
     * Update the specified payment.
     */
    public function update(PaymentRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $payment = Payment::where('user_id', $user->id)->findOrFail($id);
            $validated = $request->validated();
            $validated['date'] = isset($validated['date']) ? date('Y-m-d', strtotime($validated['date'])) : null;
            $payment->update($validated);

            return $this->sendResponse($payment, 'Payment updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Payment not found', [], 404);
        } catch (\Exception $e) {
            logError('PaymentController', 'update', $e->getMessage());
            return $this->sendError('Error updating payment', [], 500);
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $payment = Payment::where('user_id', $user->id)->findOrFail($id);
            $payment->delete();

            return $this->sendResponse([], 'Payment deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Payment not found', [], 404);
        } catch (\Exception $e) {
            logError('PaymentController', 'destroy', $e->getMessage());
            return $this->sendError('Error deleting payment', [], 500);
        }
    }

    public function paymentSources(): JsonResponse
    {
        try {
            $paymentSources = PaymentSource::select('id', 'name', 'name_en', 'name_gu', 'name_hi', 'icon')->get();
            return $this->sendResponse($paymentSources, 'Payment sources retrieved successfully');
        } catch (\Exception $e) {
            logError('PaymentController', 'paymentSources', $e->getMessage());
            return $this->sendError('Error retrieving payment sources', [], 500);
        }
    }
}
