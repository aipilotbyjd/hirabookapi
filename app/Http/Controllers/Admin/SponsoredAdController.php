<?php

namespace App\Http\Controllers\Admin;

use App\Models\SponsoredAd;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\SponsoredAdEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SponsoredAdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads = SponsoredAd::latest()->paginate(10);
        return view('admin.sponsored-ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sponsored-ads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'target_url' => 'required|url',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|image|max:1024',
            'cta_text' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:10',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle image uploads
        $imagePath = $request->file('image')->store('sponsored-ads', 'public');
        $imageUrl = Storage::url($imagePath);

        $sponsorLogoUrl = null;
        if ($request->hasFile('sponsor_logo')) {
            $sponsorLogoPath = $request->file('sponsor_logo')->store('sponsored-ads/logos', 'public');
            $sponsorLogoUrl = Storage::url($sponsorLogoPath);
        }

        // Create the sponsored ad
        SponsoredAd::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $imageUrl,
            'target_url' => $request->target_url,
            'sponsor_name' => $request->sponsor_name,
            'sponsor_logo' => $sponsorLogoUrl,
            'cta_text' => $request->cta_text ?? 'Learn More',
            'background_color' => $request->background_color ?? '#fc8019',
            'text_color' => $request->text_color ?? '#FFFFFF',
            'is_active' => $request->has('is_active'),
            'priority' => $request->priority ?? 0,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('admin.sponsored-ads.index')
            ->with('success', 'Sponsored ad created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SponsoredAd $sponsoredAd)
    {
        // Get analytics data
        $impressions = $sponsoredAd->impressions()->count();
        $clicks = $sponsoredAd->clicks()->count();
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

        // Get daily stats for the last 30 days
        $dailyStats = SponsoredAdEvent::where('sponsored_ad_id', $sponsoredAd->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, event_type, COUNT(*) as count')
            ->groupBy('date', 'event_type')
            ->get()
            ->groupBy('date');

        return view('admin.sponsored-ads.show', compact('sponsoredAd', 'impressions', 'clicks', 'ctr', 'dailyStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SponsoredAd $sponsoredAd)
    {
        return view('admin.sponsored-ads.edit', compact('sponsoredAd'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SponsoredAd $sponsoredAd)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'target_url' => 'required|url',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|image|max:1024',
            'cta_text' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:10',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle image uploads
        $imageUrl = $sponsoredAd->image_url;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sponsored-ads', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        $sponsorLogoUrl = $sponsoredAd->sponsor_logo;
        if ($request->hasFile('sponsor_logo')) {
            $sponsorLogoPath = $request->file('sponsor_logo')->store('sponsored-ads/logos', 'public');
            $sponsorLogoUrl = Storage::url($sponsorLogoPath);
        }

        // Update the sponsored ad
        $sponsoredAd->update([
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $imageUrl,
            'target_url' => $request->target_url,
            'sponsor_name' => $request->sponsor_name,
            'sponsor_logo' => $sponsorLogoUrl,
            'cta_text' => $request->cta_text ?? 'Learn More',
            'background_color' => $request->background_color ?? '#fc8019',
            'text_color' => $request->text_color ?? '#FFFFFF',
            'is_active' => $request->has('is_active'),
            'priority' => $request->priority ?? 0,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('admin.sponsored-ads.index')
            ->with('success', 'Sponsored ad updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SponsoredAd $sponsoredAd)
    {
        $sponsoredAd->delete();

        return redirect()->route('admin.sponsored-ads.index')
            ->with('success', 'Sponsored ad deleted successfully.');
    }
}
