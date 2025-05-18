@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Application Settings</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
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
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                
                <h4 class="mb-3">Application Information</h4>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="app_name" class="form-label">Application Name</label>
                        <input type="text" class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name" value="{{ old('app_name', Setting::get('app_name', 'Hirabook')) }}">
                        @error('app_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="app_version" class="form-label">Application Version</label>
                        <input type="text" class="form-control @error('app_version') is-invalid @enderror" id="app_version" name="app_version" value="{{ old('app_version', Setting::get('app_version', '1.0.0')) }}">
                        @error('app_version')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="app_description" class="form-label">Application Description</label>
                    <textarea class="form-control @error('app_description') is-invalid @enderror" id="app_description" name="app_description" rows="3">{{ old('app_description', Setting::get('app_description', 'Hirabook is a platform for hira workers')) }}</textarea>
                    @error('app_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="app_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control @error('app_email') is-invalid @enderror" id="app_email" name="app_email" value="{{ old('app_email', Setting::get('app_email', 'contact@hirabook.icu')) }}">
                        @error('app_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="app_copyright" class="form-label">Copyright Text</label>
                        <input type="text" class="form-control @error('app_copyright') is-invalid @enderror" id="app_copyright" name="app_copyright" value="{{ old('app_copyright', Setting::get('app_copyright', 'Hirabook')) }}">
                        @error('app_copyright')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="app_address" class="form-label">Address</label>
                    <input type="text" class="form-control @error('app_address') is-invalid @enderror" id="app_address" name="app_address" value="{{ old('app_address', Setting::get('app_address', 'Ahemdabad, Gujarat, India')) }}">
                    @error('app_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr class="my-4">
                
                <h4 class="mb-3">Admin Settings</h4>
                
                <div class="mb-3">
                    <label for="admin_password" class="form-label">Admin Password</label>
                    <input type="password" class="form-control @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" placeholder="Leave blank to keep current password">
                    <div class="form-text">Enter a new password only if you want to change it.</div>
                    @error('admin_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
