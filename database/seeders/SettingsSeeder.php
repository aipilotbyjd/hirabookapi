<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin password - default is 'admin123'
        Setting::set(
            'admin_password',
            Hash::make('JaydeepSp@143'),
            'admin',
            'Admin panel password'
        );

        // App settings
        Setting::set(
            'app_name',
            'Hirabook',
            'app',
            'Application name'
        );

        Setting::set(
            'app_logo',
            'https://hirabook.com/logo.png',
            'app',
            'Application logo URL'
        );

        Setting::set(
            'app_icon',
            'https://hirabook.com/icon.png',
            'app',
            'Application icon URL'
        );

        Setting::set(
            'app_description',
            'Hirabook is a platform for hira workers',
            'app',
            'Application description'
        );

        Setting::set(
            'app_version',
            '1.0.0',
            'app',
            'Application version'
        );

        Setting::set(
            'app_copyright',
            'Hirabook',
            'app',
            'Application copyright'
        );

        Setting::set(
            'app_email',
            'contact@hirabook.icu',
            'app',
            'Application contact email'
        );

        Setting::set(
            'app_address',
            'Ahemdabad, Gujarat, India',
            'app',
            'Application address'
        );
    }
}
