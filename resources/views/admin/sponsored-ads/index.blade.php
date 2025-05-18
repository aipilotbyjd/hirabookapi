@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Sponsored Ads</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.sponsored-ads.create') }}" class="btn btn-primary">Create New Ad</a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Sponsored Advertisements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title & Description</th>
                            <th>Sponsor</th>
                            <th>Schedule</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ads as $ad)
                        <tr>
                            <td>{{ $ad->id }}</td>
                            <td>
                                @if($ad->image_url)
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="img-thumbnail" style="max-width: 80px; max-height: 60px;">
                                @else
                                <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $ad->title }}</strong>
                                @if($ad->description)
                                <p class="text-muted small mb-0 mt-1">{{ Str::limit($ad->description, 100) }}</p>
                                @endif
                                <div class="small mt-1">
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="{{ $ad->target_url }}" target="_blank" class="text-decoration-none">{{ Str::limit($ad->target_url, 30) }}</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($ad->sponsor_name)
                                <div class="d-flex align-items-center">
                                    @if($ad->sponsor_logo)
                                    <img src="{{ $ad->sponsor_logo }}" alt="{{ $ad->sponsor_name }}" class="img-thumbnail me-2" style="max-width: 40px; max-height: 40px;">
                                    @endif
                                    <span>{{ $ad->sponsor_name }}</span>
                                </div>
                                @else
                                <span class="text-muted">No sponsor</span>
                                @endif
                                <div class="small mt-1">
                                    <span class="badge" style="background-color: {{ $ad->background_color }}; color: {{ $ad->text_color }};">
                                        {{ $ad->cta_text }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <strong>Priority:</strong> {{ $ad->priority }}/10
                                </div>
                                @if($ad->starts_at)
                                <div class="small">
                                    <strong>Starts:</strong> {{ $ad->starts_at->format('M d, Y') }}
                                </div>
                                @endif
                                @if($ad->ends_at)
                                <div class="small">
                                    <strong>Ends:</strong> {{ $ad->ends_at->format('M d, Y') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <strong>Impressions:</strong> {{ $ad->impressions_count ?? 0 }}
                                </div>
                                <div class="small">
                                    <strong>Clicks:</strong> {{ $ad->clicks_count ?? 0 }}
                                </div>
                                <div class="small">
                                    @php
                                        $impressions = $ad->impressions_count ?? 0;
                                        $clicks = $ad->clicks_count ?? 0;
                                        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
                                    @endphp
                                    <strong>CTR:</strong> {{ $ctr }}%
                                </div>
                            </td>
                            <td>
                                @if($ad->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif

                                @php
                                    $now = now();
                                    $isScheduled = $ad->starts_at && $ad->starts_at->gt($now);
                                    $isExpired = $ad->ends_at && $ad->ends_at->lt($now);
                                @endphp

                                @if($isScheduled)
                                <span class="badge bg-info">Scheduled</span>
                                @elseif($isExpired)
                                <span class="badge bg-warning text-dark">Expired</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.sponsored-ads.show', $ad) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.sponsored-ads.edit', $ad) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.sponsored-ads.destroy', $ad) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this ad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No sponsored ads found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $ads->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
