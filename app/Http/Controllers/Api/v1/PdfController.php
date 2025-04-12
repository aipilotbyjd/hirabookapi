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
            $work = Work::where('user_id', Auth::id())
                ->with(['workItems', 'user'])
                ->findOrFail($id);

            $pdf = PDF::loadView('pdfs.work', ['work' => $work]);
            return $pdf->download("work-{$id}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportUserWorks()
    {
        try {
            $works = Work::with(['workItems', 'user'])
                ->where('user_id', Auth::id())
                ->get();

            $pdf = PDF::loadView('pdfs.works', ['works' => $works]);
            return $pdf->download("works-" . Auth::id() . ".pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportPayment($id)
    {
        try {
            $payment = Payment::where('user_id', Auth::id())
                ->with(['user', 'source'])
                ->findOrFail($id);

            $pdf = PDF::loadView('pdfs.payment', ['payment' => $payment]);
            return $pdf->download("payment-{$id}.pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }

    public function exportUserPayments()
    {
        try {
            $payments = Payment::with(['user', 'source'])
                ->where('user_id', Auth::id())
                ->get();

            $pdf = PDF::loadView('pdfs.payments', ['payments' => $payments]);
            return $pdf->download("payments-" . Auth::id() . ".pdf");
        } catch (\Exception $e) {
            return $this->sendError('Error generating PDF', [], 500);
        }
    }
}
