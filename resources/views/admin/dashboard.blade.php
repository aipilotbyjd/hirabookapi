@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Admin Dashboard</h1>
        </div>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-megaphone-fill text-primary me-2"></i>
                        Sponsored Ads
                    </h5>
                    <p class="card-text">Manage sponsored advertisements that appear in the application.</p>
                    <a href="{{ route('admin.sponsored-ads.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Manage Ads
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-gear-fill text-secondary me-2"></i>
                        Settings
                    </h5>
                    <p class="card-text">Configure application settings and admin password.</p>
                    <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right"></i> Manage Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
