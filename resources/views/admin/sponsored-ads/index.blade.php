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
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Impressions</th>
                        <th>Clicks</th>
                        <th>CTR</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ads as $ad)
                    <tr>
                        <td>{{ $ad->title }}</td>
                        <td>
                            @if($ad->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $ad->priority }}</td>
                        <td>{{ $ad->impressions_count ?? 0 }}</td>
                        <td>{{ $ad->clicks_count ?? 0 }}</td>
                        <td>
                            @php
                                $impressions = $ad->impressions_count ?? 0;
                                $clicks = $ad->clicks_count ?? 0;
                                $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
                            @endphp
                            {{ $ctr }}%
                        </td>
                        <td>
                            <a href="{{ route('admin.sponsored-ads.show', $ad) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.sponsored-ads.edit', $ad) }}" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $ads->links() }}
        </div>
    </div>
</div>
@endsection