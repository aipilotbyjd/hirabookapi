<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Session::has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $adminPassword = Setting::get('admin_password');

        if (!$adminPassword) {
            return redirect()->route('admin.login')
                ->with('error', 'Admin password not set. Please contact the system administrator.');
        }

        if (Hash::check($request->password, $adminPassword)) {
            // Set admin session
            Session::put('admin_logged_in', true);
            Session::put('admin_login_time', now());

            return redirect()->route('admin.dashboard')
                ->with('success', 'Login successful');
        }

        return redirect()->route('admin.login')
            ->with('error', 'Invalid password');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Handle admin logout
     */
    public function logout()
    {
        Session::forget('admin_logged_in');
        Session::forget('admin_login_time');

        return redirect()->route('admin.login')
            ->with('success', 'Logged out successfully');
    }

    /**
     * Show settings form
     */
    public function showSettings()
    {
        $settings = Setting::where('group', 'app')->get();
        $adminPassword = Setting::where('key', 'admin_password')->first();

        return view('admin.settings', compact('settings', 'adminPassword'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_email' => 'required|email|max:255',
            'admin_password' => 'nullable|string|min:6',
        ]);

        // Update app settings
        foreach ($request->except(['_token', 'admin_password']) as $key => $value) {
            Setting::set($key, $value, 'app');
        }

        // Update admin password if provided
        if ($request->filled('admin_password')) {
            Setting::set('admin_password', Hash::make($request->admin_password), 'admin', 'Admin panel password');
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully');
    }
}
