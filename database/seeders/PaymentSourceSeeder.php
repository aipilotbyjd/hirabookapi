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
            ['name_en' => 'Cash', 'name_gu' => 'રોકડ', 'name_hi' => 'नकद', 'icon' => 'cash'],
            ['name_en' => 'Bank Transfer', 'name_gu' => 'બેંક ટ્રાન્સફર', 'name_hi' => 'बैंक ट्रांसफर', 'icon' => 'bank-transfer-in'],
            ['name_en' => 'UPI', 'name_gu' => 'યુપીઆઈ', 'name_hi' => 'यूपीआई', 'icon' => 'qrcode'],
            ['name_en' => 'Cheque', 'name_gu' => 'ચેક', 'name_hi' => 'चेक', 'icon' => 'file-document-outline'],
            ['name_en' => 'Card', 'name_gu' => 'કાર્ડ', 'name_hi' => 'कार्ड', 'icon' => 'credit-card'],
            ['name_en' => 'Other', 'name_gu' => 'અન્ય', 'name_hi' => 'अन्य', 'icon' => 'dots-horizontal'],
        ];

        foreach ($paymentSources as $paymentSource) {
            PaymentSource::create($paymentSource);
        }
    }
}
