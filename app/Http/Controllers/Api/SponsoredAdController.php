<?php

namespace App\Http\Controllers\Api;

use App\Models\SponsoredAd;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\SponsoredAdEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\SponsoredAdResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SponsoredAdController extends Controller
{
    /**
     * Get active sponsored ads.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $ads = SponsoredAd::active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return SponsoredAdResource::collection($ads);
    }

    /**
     * Track an impression for a sponsored ad.
     *
     * @param Request $request
     * @param SponsoredAd $sponsoredAd
     * @return Response
     */
    public function trackImpression(Request $request, SponsoredAd $sponsoredAd): Response
    {
        // Increment the impressions count
        $sponsoredAd->incrementImpressions();

        // Create an event record
        SponsoredAdEvent::create([
            'sponsored_ad_id' => $sponsoredAd->id,
            'user_id' => $request->user()?->id,
            'event_type' => 'impression',
            'device_type' => $request->header('X-Device-Type'),
            'device_id' => $request->header('X-Device-ID'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'referrer' => $request->header('Referer'),
                'screen_size' => $request->header('X-Screen-Size'),
            ],
        ]);

        return response()->noContent();
    }

    /**
     * Track a click for a sponsored ad.
     *
     * @param Request $request
     * @param SponsoredAd $sponsoredAd
     * @return Response
     */
    public function trackClick(Request $request, SponsoredAd $sponsoredAd): Response
    {
        // Increment the clicks count
        $sponsoredAd->incrementClicks();

        // Create an event record
        SponsoredAdEvent::create([
            'sponsored_ad_id' => $sponsoredAd->id,
            'user_id' => $request->user()?->id,
            'event_type' => 'click',
            'device_type' => $request->header('X-Device-Type'),
            'device_id' => $request->header('X-Device-ID'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'referrer' => $request->header('Referer'),
                'screen_size' => $request->header('X-Screen-Size'),
            ],
        ]);

        return response()->noContent();
    }
}
