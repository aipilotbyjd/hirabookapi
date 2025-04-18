<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\BaseController;
use App\Models\PaymentSource;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Work;
use Illuminate\Support\Facades\Auth;
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
}

