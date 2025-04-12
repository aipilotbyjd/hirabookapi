<?php

namespace Database\Seeders;

use App\Models\SponsoredAd;
use Illuminate\Database\Seeder;

class SponsoredAdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some active ads
        SponsoredAd::factory()->count(5)->active()->highPriority()->create();

        // Create some scheduled ads
        SponsoredAd::factory()->count(2)->scheduled()->create();

        // Create some expired ads
        SponsoredAd::factory()->count(2)->expired()->create();

        // Create some inactive ads
        SponsoredAd::factory()->count(1)->inactive()->create();

        // Create some predefined ads
        $this->createPredefinedAds();
    }

    /**
     * Create predefined sponsored ads.
     */
    private function createPredefinedAds(): void
    {
        $ads = [
            [
                'title' => 'Premium Construction Tools',
                'description' => 'Get 20% off on all premium construction tools this month',
                'image_url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                'target_url' => 'https://example.com/construction-tools',
                'sponsor_name' => 'ToolMaster Pro',
                'sponsor_logo' => 'https://ui-avatars.com/api/?name=TM&background=FF5722&color=fff',
                'cta_text' => 'Shop Now',
                'background_color' => '#FF5722',
                'priority' => 10,
            ],
            [
                'title' => 'Professional Work Gear',
                'description' => 'Durable work clothes for construction professionals',
                'image_url' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=800&q=80',
                'target_url' => 'https://example.com/work-gear',
                'sponsor_name' => 'SafetyFirst Apparel',
                'sponsor_logo' => 'https://ui-avatars.com/api/?name=SF&background=4CAF50&color=fff',
                'cta_text' => 'View Collection',
                'background_color' => '#4CAF50',
                'priority' => 9,
            ],
            [
                'title' => 'Construction Management Software',
                'description' => 'Streamline your projects with our easy-to-use software',
                'image_url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=800&q=80',
                'target_url' => 'https://example.com/management-software',
                'sponsor_name' => 'BuildTrack Solutions',
                'sponsor_logo' => 'https://ui-avatars.com/api/?name=BT&background=2196F3&color=fff',
                'cta_text' => 'Try Free Demo',
                'background_color' => '#2196F3',
                'priority' => 8,
            ],
            [
                'title' => 'Heavy Equipment Rental',
                'description' => 'Rent high-quality construction equipment at competitive prices',
                'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                'target_url' => 'https://example.com/equipment-rental',
                'sponsor_name' => 'MegaRent Equipment',
                'sponsor_logo' => 'https://ui-avatars.com/api/?name=MR&background=FFC107&color=fff',
                'cta_text' => 'Get a Quote',
                'background_color' => '#FFC107',
                'priority' => 7,
            ],
        ];

        foreach ($ads as $ad) {
            SponsoredAd::create(array_merge($ad, [
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addMonths(3),
            ]));
        }
    }
}
