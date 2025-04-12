<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\BaseController;
use App\Models\Work;
use App\Models\Payment;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Auth;

class PdfController extends BaseController
{
    public function exportWork($id)
    {
        try {
            $work = Work::with(['workItems', 'user'])->findOrFail($id);

            $pdf = PDF::loadView('pdfs.work', ['work' => $work]);
            return $pdf->download("work-{$id}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportUserWorks($userId = null)
    {
        try {
            $userId = $userId ?? Auth::id();
            $works = Work::with(['workItems', 'user'])
                ->where('user_id', $userId)
                ->get();

            $pdf = PDF::loadView('pdfs.works', ['works' => $works]);
            return $pdf->download("works-{$userId}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportPayment($id)
    {
        try {
            $payment = Payment::with(['user', 'source'])->findOrFail($id);

            $pdf = PDF::loadView('pdfs.payment', ['payment' => $payment]);
            return $pdf->download("payment-{$id}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportUserPayments($userId = null)
    {
        try {
            $userId = $userId ?? Auth::id();
            $payments = Payment::with(['user', 'source'])
                ->where('user_id', $userId)
                ->get();

            $pdf = PDF::loadView('pdfs.payments', ['payments' => $payments]);
            return $pdf->download("payments-{$userId}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }
}
