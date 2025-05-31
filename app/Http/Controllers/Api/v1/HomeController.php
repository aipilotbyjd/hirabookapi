<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\BaseController;
use App\Models\PaymentSource;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Work;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function settings()
    {
        try {
            $paymentSources = PaymentSource::select('id', 'name', 'name_en', 'name_gu', 'name_hi', 'icon')->get();

            $settings = [
                'app_name' => 'Hirabook',
                'app_logo' => 'https://hirabook.com/logo.png',
                'app_icon' => 'https://hirabook.com/icon.png',
                'app_description' => 'Hirabook is a platform for hira workers',
                'app_version' => '1.0.0',
                'app_copyright' => 'Hirabook',
                'app_email' => 'contact@hirabook.icu',
                'app_address' => 'Ahemdabad, Gujarat, India',
                'app_phone' => '',
                'app_payment_sources' => $paymentSources,
            ];

            return $this->sendResponse($settings, 'Settings fetched successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'settings', $e->getMessage());
            return $this->sendError('Error fetching settings', [], 500);
        }
    }

    public function notifications()
    {
        try {
            $perPage = request()->query('per_page', 10);
            $notifications = Notification::select('id', 'receiver_id', 'title', 'description', 'image', 'is_read', 'link', 'link_text', 'link_icon', 'link_color', 'created_at')
                ->where(function ($query) {
                    $query->where('receiver_id', Auth::user()->id)
                        ->orWhereNull('receiver_id');
                })
                ->latest()
                ->paginate($perPage);

            return $this->sendResponse($notifications, 'Notifications fetched successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'notifications', $e->getMessage());
            return $this->sendError('Error fetching notifications', [], 500);
        }
    }

    public function readNotification($id, $isRead = true)
    {
        try {
            $notification = Notification::where('id', $id)->where(function ($query) {
                $query->where('receiver_id', Auth::user()->id)
                    ->orWhereNull('receiver_id');
            })->first();

            if (!$notification) {
                return $this->sendError('Notification not found', [], 404);
            }

            $notification->is_read = $isRead == 'true' ? true : false;
            $notification->save();
            return $this->sendResponse($notification, 'Notification read successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'readNotification', $e->getMessage());
            return $this->sendError('Error reading notification', [], 500);
        }
    }

    public function readAllNotifications()
    {
        try {
            Notification::where(function ($query) {
                $query->where('receiver_id', Auth::user()->id)
                    ->orWhereNull('receiver_id');
            })->where('is_read', false)->update(['is_read' => true]);
            return $this->sendResponse([], 'All notifications read successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'readAllNotifications', $e->getMessage());
            return $this->sendError('Error reading all notifications', [], 500);
        }
    }

    public function unreadNotificationsCount()
    {
        try {
            $count = Notification::where(function ($query) {
                $query->where(function ($query) {
                    $query->where('receiver_id', Auth::user()->id)
                        ->unread();
                })->orWhereNull('receiver_id');
            })->count();

            return $this->sendResponse($count, 'Unread notifications count fetched successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'unreadNotificationsCount', $e->getMessage());
            return $this->sendError('Error fetching unread notifications count', [], 500);
        }
    }

    public function getRecentActivities()
    {
        try {
            // Get recent works with their items
            $works = Work::with('workItems')
                ->where('user_id', Auth::id())
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($work) {
                    return [
                        'id' => $work->id,
                        'type' => 'work',
                        'from' => $work->name,
                        'title' => $work->name,
                        'description' => $work->description,
                        'amount' => $work->total,
                        'created_at' => $work->created_at,
                    ];
                });

            // Get recent payments
            $payments = Payment::latest()
                ->where('user_id', Auth::id())
                ->take(10)
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'type' => 'payment',
                        'title' => $payment->from,
                        'from' => $payment->from,
                        'description' => $payment->description,
                        'amount' => $payment->amount,
                        'created_at' => $payment->created_at,
                    ];
                });

            // Merge and sort activities by date
            $activities = $works->concat($payments)
                ->sortByDesc('created_at')
                ->take(10)
                ->values();

            return $this->sendResponse($activities, 'Recent activities fetched successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'getRecentActivities', $e->getMessage());
            return $this->sendError('Error fetching recent activities', [], 500);
        }
    }

    public function getRecentStatus()
    {
        try {
            // Get works stats with single query
            $worksStats = Work::selectRaw('
                COUNT(*) as total_works,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_works,
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as weekly_works,
                COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN 1 END) as monthly_works
            ', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('user_id', Auth::id())
                ->first();

            // Get total work amount
            $totalWorkAmount = Work::getTotalWorkAmount('all');
            $todayWorkAmount = Work::getTotalWorkAmount('today');
            $weeklyWorkAmount = Work::getTotalWorkAmount('week');
            $monthlyWorkAmount = Work::getTotalWorkAmount('month');

            // Get payments stats with single query
            $paymentsStats = Payment::selectRaw('
                COUNT(*) as total_payments,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_payments,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN amount ELSE 0 END) as today_amount,
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as weekly_payments,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN amount ELSE 0 END) as weekly_amount,
                COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN 1 END) as monthly_payments,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN amount ELSE 0 END) as monthly_amount,
                SUM(amount) as total_amount
            ', [
                now()->startOfWeek(),
                now()->endOfWeek(),
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
                ->where('user_id', Auth::id())
                ->first();

            $status = [
                'today' => [
                    'works' => $worksStats->today_works,
                    'work_amount' => $todayWorkAmount,
                    'payments' => $paymentsStats->today_payments,
                    'total_amount' => $paymentsStats->today_amount
                ],
                'weekly' => [
                    'works' => $worksStats->weekly_works,
                    'work_amount' => $weeklyWorkAmount,
                    'payments' => $paymentsStats->weekly_payments,
                    'total_amount' => $paymentsStats->weekly_amount
                ],
                'monthly' => [
                    'works' => $worksStats->monthly_works,
                    'work_amount' => $monthlyWorkAmount,
                    'payments' => $paymentsStats->monthly_payments,
                    'total_amount' => $paymentsStats->monthly_amount
                ],
                'total_works' => $worksStats->total_works,
                'total_work_amount' => $totalWorkAmount,
                'total_payments' => $paymentsStats->total_payments,
                'total_amount' => $paymentsStats->total_amount
            ];

            return $this->sendResponse($status, 'Recent status fetched successfully');
        } catch (\Exception $e) {
            logError('HomeController', 'getRecentStatus', $e->getMessage());
            return $this->sendError('Error fetching recent status', [], 500);
        }
    }

    public function generateReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $reportType = $request->input('report_type'); // 'work', 'payment', or null for merged
            $jobType = $request->input('job_type'); // filter for work items
            $searchTerm = $request->input(key: 'search'); // New search term parameter

            $responseData = [];
            $workSummary = null;
            $paymentSummary = null;

            $shouldFetchWorks = !$reportType || $reportType === 'work';
            $shouldFetchPayments = !$reportType || $reportType === 'payment';

            if ($shouldFetchWorks) {
                $workQuery = Work::where('user_id', Auth::id())
                    ->when($startDate, function ($query) use ($startDate) {
                        return $query->whereDate('created_at', '>=', $startDate);
                    })
                    ->when($endDate, function ($query) use ($endDate) {
                        return $query->whereDate('created_at', '<=', $endDate);
                    })
                    ->when($jobType, function ($query) use ($jobType) {
                        return $query->whereHas('workItems', function ($q) use ($jobType) {
                            $q->where('type', $jobType);
                        });
                    })
                    ->when($searchTerm, function ($query) use ($searchTerm) {
                        return $query->where(function ($q) use ($searchTerm) {
                            $q->where('name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('total', 'like', '%' . $searchTerm . '%')
                                ->orWhereHas('workItems', function ($wiQuery) use ($searchTerm) {
                                    $wiQuery->where('type', 'like', '%' . $searchTerm . '%')
                                        ->orWhere('diamond', 'like', '%' . $searchTerm . '%')
                                        ->orWhere('price', 'like', '%' . $searchTerm . '%');
                                });
                        });
                    })
                    ->with('workItems');

                $works = $workQuery->get();
                $responseData['works'] = $works;
                $workSummary = [
                    'total_records' => $works->count(),
                    'total_amount' => $works->sum('total'),
                ];
            }

            if ($shouldFetchPayments) {
                $paymentQuery = Payment::where('user_id', Auth::id())
                    ->when($startDate, function ($query) use ($startDate) {
                        return $query->whereDate('created_at', '>=', $startDate);
                    })
                    ->when($endDate, function ($query) use ($endDate) {
                        return $query->whereDate('created_at', '<=', $endDate);
                    })
                    ->when($searchTerm, function ($query) use ($searchTerm) {
                        return $query->where(function ($q) use ($searchTerm) {
                            $q->where('from', 'like', '%' . $searchTerm . '%')
                                ->orWhere('amount', 'like', '%' . $searchTerm . '%')
                                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                                ->orWhereHas('payment_sources', function ($wQuery) use ($searchTerm) {
                                    $wQuery->where('name', 'like', '%' . $searchTerm . '%')
                                        ->orWhere('name_en', 'like', '%' . $searchTerm . '%')
                                        ->orWhere('name_gu', 'like', '%' . $searchTerm . '%')
                                        ->orWhere('name_hi', 'like', '%' . $searchTerm . '%');
                                });
                        });
                    });

                $payments = $paymentQuery->get();
                $responseData['payments'] = $payments;
                $paymentSummary = [
                    'total_records' => $payments->count(),
                    'total_amount' => $payments->sum('amount'),
                ];
            }

            $finalSummary = [];
            if ($workSummary) {
                $finalSummary['work'] = $workSummary;
            }
            if ($paymentSummary) {
                $finalSummary['payment'] = $paymentSummary;
            }

            return $this->sendResponse([
                'records' => $responseData,
                'summary' => $finalSummary,
            ], 'Report generated successfully');

        } catch (\Exception $e) {
            logError('HomeController', 'generateReport', $e->getMessage());
            return $this->sendError('Error generating report', [], 500);
        }
    }
}

