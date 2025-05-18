@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Sponsored Ad Details</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.sponsored-ads.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.sponsored-ads.edit', $sponsoredAd) }}" class="btn btn-primary">Edit</a>
            <form action="{{ route('admin.sponsored-ads.destroy', $sponsoredAd) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this ad?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ad Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Title:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $sponsoredAd->title }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Description:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $sponsoredAd->description ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Image:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($sponsoredAd->image_url)
                            <img src="{{ $sponsoredAd->image_url }}" alt="{{ $sponsoredAd->title }}" class="img-fluid" style="max-height: 200px;">
                            @else
                            No image
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Target URL:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="{{ $sponsoredAd->target_url }}" target="_blank">{{ $sponsoredAd->target_url }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Sponsor:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($sponsoredAd->sponsor_name)
                            {{ $sponsoredAd->sponsor_name }}
                            @if($sponsoredAd->sponsor_logo)
                            <br>
                            <img src="{{ $sponsoredAd->sponsor_logo }}" alt="{{ $sponsoredAd->sponsor_name }}" class="img-thumbnail" style="max-height: 50px;">
                            @endif
                            @else
                            N/A
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>CTA Text:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $sponsoredAd->cta_text }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Colors:</strong>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div style="width: 30px; height: 30px; background-color: {{ $sponsoredAd->background_color }}; margin-right: 10px; border: 1px solid #ddd;"></div>
                                <span>Background: {{ $sponsoredAd->background_color }}</span>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <div style="width: 30px; height: 30px; background-color: {{ $sponsoredAd->text_color }}; margin-right: 10px; border: 1px solid #ddd;"></div>
                                <span>Text: {{ $sponsoredAd->text_color }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($sponsoredAd->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Priority:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $sponsoredAd->priority }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Duration:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($sponsoredAd->starts_at)
                            From {{ $sponsoredAd->starts_at->format('M d, Y H:i') }}
                            @else
                            No start date
                            @endif
                            <br>
                            @if($sponsoredAd->ends_at)
                            To {{ $sponsoredAd->ends_at->format('M d, Y H:i') }}
                            @else
                            No end date
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Performance</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 text-center">
                            <h3>{{ $impressions }}</h3>
                            <p class="text-muted">Impressions</p>
                        </div>
                        <div class="col-6 text-center">
                            <h3>{{ $clicks }}</h3>
                            <p class="text-muted">Clicks</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-center">
                            <h3>{{ $ctr }}%</h3>
                            <p class="text-muted">Click-Through Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
