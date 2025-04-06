<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentSources = [
            ['name_en' => 'Cash', 'name_gu' => 'Cash', 'name_hi' => 'Cash', 'icon' => 'cash'],
            ['name_en' => 'Bank Transfer', 'name_gu' => 'Bank Transfer', 'name_hi' => 'Bank Transfer', 'icon' => 'bank-transfer-in'],
            ['name_en' => 'UPI', 'name_gu' => 'UPI', 'name_hi' => 'UPI', 'icon' => 'qrcode'],
            ['name_en' => 'Cheque', 'name_gu' => 'Cheque', 'name_hi' => 'Cheque', 'icon' => 'file-document-outline'],
            ['name_en' => 'Card', 'name_gu' => 'Card', 'name_hi' => 'Card', 'icon' => 'credit-card'],
            ['name_en' => 'Other', 'name_gu' => 'Other', 'name_hi' => 'Other', 'icon' => 'dots-horizontal'],
        ];

        foreach ($paymentSources as $paymentSource) {
            PaymentSource::create($paymentSource);
        }
    }
}
