<?php

namespace App\Http\Controllers;

use App\Models\SponsoredAd;
use Illuminate\Http\Request;
use App\Models\SponsoredAdEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SponsoredAdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SponsoredAd::query();

        // Filter by status if provided
        $status = $request->input('status');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'scheduled') {
            $query->where('is_active', true)
                ->where('starts_at', '>', now());
        } elseif ($status === 'expired') {
            $query->where('is_active', true)
                ->where('ends_at', '<', now());
        }

        // Default sorting
        $ads = $query->latest()->paginate(10);

        // Get counts for the filter tabs
        $counts = [
            'all' => SponsoredAd::count(),
            'active' => SponsoredAd::active()->count(),
            'inactive' => SponsoredAd::where('is_active', false)->count(),
            'scheduled' => SponsoredAd::where('is_active', true)->where('starts_at', '>', now())->count(),
            'expired' => SponsoredAd::where('is_active', true)->where('ends_at', '<', now())->count(),
        ];

        return view('admin.sponsored-ads.index', compact('ads', 'status', 'counts'));
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
            'description' => 'nullable|string|max:1000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=300,min_height=200,max_width=2000,max_height=1500',
            'target_url' => 'required|url|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:min_width=50,min_height=50,max_width=500,max_height=500',
            'cta_text' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:10',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], [
            'image.required' => 'The advertisement image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'image.dimensions' => 'The image dimensions must be between 300x200 and 2000x1500 pixels.',
            'sponsor_logo.image' => 'The sponsor logo must be an image.',
            'sponsor_logo.mimes' => 'The sponsor logo must be a file of type: jpeg, png, jpg, gif.',
            'sponsor_logo.max' => 'The sponsor logo may not be greater than 1MB.',
            'sponsor_logo.dimensions' => 'The sponsor logo dimensions must be between 50x50 and 500x500 pixels.',
            'target_url.url' => 'Please enter a valid URL including http:// or https://.',
            'ends_at.after_or_equal' => 'The end date must be after or equal to the start date.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle image uploads
            $imagePath = $request->file('image')->store('sponsored-ads', 'public');
            $imageUrl = Storage::url($imagePath);

            $sponsorLogoUrl = null;
            if ($request->hasFile('sponsor_logo')) {
                $sponsorLogoPath = $request->file('sponsor_logo')->store('sponsored-ads/logos', 'public');
                $sponsorLogoUrl = Storage::url($sponsorLogoPath);
            }

            // Create the sponsored ad
            $sponsoredAd = SponsoredAd::create([
                'title' => $request->title,
                'description' => $request->description,
                'image_url' => $imageUrl,
                'target_url' => $request->target_url,
                'sponsor_name' => $request->sponsor_name,
                'sponsor_logo' => $sponsorLogoUrl,
                'cta_text' => $request->cta_text ?? 'Learn More',
                'background_color' => $request->background_color ?? '#fc8019',
                'text_color' => $request->text_color ?? '#FFFFFF',
                'is_active' => (bool) $request->input('is_active', false),
                'priority' => $request->priority ?? 0,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
            ]);

            return redirect()->route('admin.sponsored-ads.index')
                ->with('success', 'Sponsored advertisement "' . $sponsoredAd->title . '" has been created successfully.');
        } catch (\Exception $e) {
            // If there was an error, clean up any uploaded files
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            if (isset($sponsorLogoPath) && Storage::disk('public')->exists($sponsorLogoPath)) {
                Storage::disk('public')->delete($sponsorLogoPath);
            }

            return redirect()->back()
                ->with('error', 'Failed to create sponsored advertisement. Error: ' . $e->getMessage())
                ->withInput();
        }
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
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=300,min_height=200,max_width=2000,max_height=1500',
            'target_url' => 'required|url|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:min_width=50,min_height=50,max_width=500,max_height=500',
            'cta_text' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:10',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'remove_image' => 'nullable|boolean',
            'remove_sponsor_logo' => 'nullable|boolean',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'image.dimensions' => 'The image dimensions must be between 300x200 and 2000x1500 pixels.',
            'sponsor_logo.image' => 'The sponsor logo must be an image.',
            'sponsor_logo.mimes' => 'The sponsor logo must be a file of type: jpeg, png, jpg, gif.',
            'sponsor_logo.max' => 'The sponsor logo may not be greater than 1MB.',
            'sponsor_logo.dimensions' => 'The sponsor logo dimensions must be between 50x50 and 500x500 pixels.',
            'target_url.url' => 'Please enter a valid URL including http:// or https://.',
            'ends_at.after_or_equal' => 'The end date must be after or equal to the start date.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Store original image paths in case we need to revert
            $originalImageUrl = $sponsoredAd->image_url;
            $originalSponsorLogoUrl = $sponsoredAd->sponsor_logo;

            // New image paths
            $imageUrl = $originalImageUrl;
            $sponsorLogoUrl = $originalSponsorLogoUrl;

            // Handle main image
            if ($request->boolean('remove_image')) {
                // Delete the existing image
                $sponsoredAd->deleteImage();
                $imageUrl = null;
            } elseif ($request->hasFile('image')) {
                // Upload new image
                $imagePath = $request->file('image')->store('sponsored-ads', 'public');
                $imageUrl = Storage::url($imagePath);

                // Delete the old image after successful upload
                if ($originalImageUrl) {
                    $sponsoredAd->deleteImage();
                }
            }

            // Handle sponsor logo
            if ($request->boolean('remove_sponsor_logo')) {
                // Delete the existing logo
                $sponsoredAd->deleteSponsorLogo();
                $sponsorLogoUrl = null;
            } elseif ($request->hasFile('sponsor_logo')) {
                // Upload new logo
                $sponsorLogoPath = $request->file('sponsor_logo')->store('sponsored-ads/logos', 'public');
                $sponsorLogoUrl = Storage::url($sponsorLogoPath);

                // Delete the old logo after successful upload
                if ($originalSponsorLogoUrl) {
                    $sponsoredAd->deleteSponsorLogo();
                }
            }

            // Validate that we have an image if required
            if (!$imageUrl) {
                return redirect()->back()
                    ->withErrors(['image' => 'An advertisement image is required. Please upload a new image.'])
                    ->withInput();
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
                'is_active' => (bool) $request->input('is_active', false),
                'priority' => $request->priority ?? 0,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
            ]);

            return redirect()->route('admin.sponsored-ads.index')
                ->with('success', 'Sponsored advertisement "' . $sponsoredAd->title . '" has been updated successfully.');
        } catch (\Exception $e) {
            // If there was an error, clean up any newly uploaded files
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            if (isset($sponsorLogoPath) && Storage::disk('public')->exists($sponsorLogoPath)) {
                Storage::disk('public')->delete($sponsorLogoPath);
            }

            return redirect()->back()
                ->with('error', 'Failed to update sponsored advertisement. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SponsoredAd $sponsoredAd)
    {
        try {
            $adTitle = $sponsoredAd->title;

            // The model's booted method will handle deleting the images
            $sponsoredAd->delete();

            return redirect()->route('admin.sponsored-ads.index')
                ->with('success', 'Sponsored advertisement "' . $adTitle . '" has been deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sponsored-ads.index')
                ->with('error', 'Failed to delete the sponsored advertisement. Error: ' . $e->getMessage());
        }
    }
}


