@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Create Sponsored Ad</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.sponsored-ads.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="alert alert-info">
        <h5><i class="bi bi-info-circle"></i> Image Requirements</h5>
        <ul class="mb-0">
            <li>Main Image: JPEG, PNG, JPG, or GIF format, max 2MB, dimensions between 300x200 and 2000x1500 pixels</li>
            <li>Sponsor Logo: JPEG, PNG, JPG, or GIF format, max 1MB, dimensions between 50x50 and 500x500 pixels</li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.sponsored-ads.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Advertisement Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="image" name="image" required accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewImage(this, 'imagePreview')">
                            <div class="mt-2" id="imagePreview"></div>
                            <small class="text-muted">Required. JPEG, PNG, JPG, or GIF format, max 2MB, dimensions between 300x200 and 2000x1500 pixels</small>
                        </div>
                        <div class="mb-3">
                            <label for="target_url" class="form-label">Target URL</label>
                            <input type="url" class="form-control" id="target_url" name="target_url" value="{{ old('target_url') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sponsor_name" class="form-label">Sponsor Name</label>
                            <input type="text" class="form-control" id="sponsor_name" name="sponsor_name" value="{{ old('sponsor_name') }}">
                        </div>
                        <div class="mb-3">
                            <label for="sponsor_logo" class="form-label">Sponsor Logo</label>
                            <input type="file" class="form-control" id="sponsor_logo" name="sponsor_logo" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewImage(this, 'sponsorLogoPreview')">
                            <div class="mt-2" id="sponsorLogoPreview"></div>
                            <small class="text-muted">Optional. JPEG, PNG, JPG, or GIF format, max 1MB, dimensions between 50x50 and 500x500 pixels</small>
                        </div>
                        <div class="mb-3">
                            <label for="cta_text" class="form-label">CTA Text</label>
                            <input type="text" class="form-control" id="cta_text" name="cta_text" value="{{ old('cta_text', 'Learn More') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Background Color</label>
                                    <input type="color" class="form-control" id="background_color" name="background_color" value="{{ old('background_color', '#fc8019') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color</label>
                                    <input type="color" class="form-control" id="text_color" name="text_color" value="{{ old('text_color', '#FFFFFF') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority (0-10)</label>
                            <input type="number" class="form-control" id="priority" name="priority" min="0" max="10" value="{{ old('priority', 0) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="starts_at" class="form-label">Start Date</label>
                            <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="ends_at" class="form-label">End Date</label>
                            <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="{{ old('ends_at') }}">
                        </div>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary">Create Ad</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.maxHeight = '200px';
                preview.appendChild(img);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');

        form.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate title
            const title = document.getElementById('title');
            if (!title.value.trim()) {
                isValid = false;
                title.classList.add('is-invalid');
            } else {
                title.classList.remove('is-invalid');
            }

            // Validate image
            const image = document.getElementById('image');
            if (image.required && !image.files.length) {
                isValid = false;
                image.classList.add('is-invalid');
            } else {
                image.classList.remove('is-invalid');
            }

            // Validate target URL
            const targetUrl = document.getElementById('target_url');
            if (!targetUrl.value.trim() || !targetUrl.checkValidity()) {
                isValid = false;
                targetUrl.classList.add('is-invalid');
            } else {
                targetUrl.classList.remove('is-invalid');
            }

            if (!isValid) {
                event.preventDefault();
                alert('Please correct the errors in the form before submitting.');
            }
        });
    });
</script>
@endpush

@section('styles')
<style>
    .is-invalid {
        border-color: #dc3545;
    }

    .img-preview {
        margin-top: 10px;
    }
</style>
@endsection

@endsection
