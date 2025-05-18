@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Edit Sponsored Ad</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.sponsored-ads.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.sponsored-ads.update', $sponsoredAd) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $sponsoredAd->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $sponsoredAd->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            @if($sponsoredAd->image_url)
                            <div class="mb-2">
                                <img src="{{ $sponsoredAd->image_url }}" alt="{{ $sponsoredAd->title }}" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                            @endif
                            <input type="file" class="form-control" id="image" name="image">
                            <small class="text-muted">Leave empty to keep the current image</small>
                        </div>
                        <div class="mb-3">
                            <label for="target_url" class="form-label">Target URL</label>
                            <input type="url" class="form-control" id="target_url" name="target_url" value="{{ old('target_url', $sponsoredAd->target_url) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sponsor_name" class="form-label">Sponsor Name</label>
                            <input type="text" class="form-control" id="sponsor_name" name="sponsor_name" value="{{ old('sponsor_name', $sponsoredAd->sponsor_name) }}">
                        </div>
                        <div class="mb-3">
                            <label for="sponsor_logo" class="form-label">Sponsor Logo</label>
                            @if($sponsoredAd->sponsor_logo)
                            <div class="mb-2">
                                <img src="{{ $sponsoredAd->sponsor_logo }}" alt="{{ $sponsoredAd->sponsor_name }}" class="img-thumbnail" style="max-height: 50px;">
                            </div>
                            @endif
                            <input type="file" class="form-control" id="sponsor_logo" name="sponsor_logo">
                            <small class="text-muted">Leave empty to keep the current logo</small>
                        </div>
                        <div class="mb-3">
                            <label for="cta_text" class="form-label">CTA Text</label>
                            <input type="text" class="form-control" id="cta_text" name="cta_text" value="{{ old('cta_text', $sponsoredAd->cta_text) }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Background Color</label>
                                    <input type="color" class="form-control" id="background_color" name="background_color" value="{{ old('background_color', $sponsoredAd->background_color) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color</label>
                                    <input type="color" class="form-control" id="text_color" name="text_color" value="{{ old('text_color', $sponsoredAd->text_color) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority (0-10)</label>
                            <input type="number" class="form-control" id="priority" name="priority" min="0" max="10" value="{{ old('priority', $sponsoredAd->priority) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="starts_at" class="form-label">Start Date</label>
                            <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at', $sponsoredAd->starts_at ? $sponsoredAd->starts_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="ends_at" class="form-label">End Date</label>
                            <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="{{ old('ends_at', $sponsoredAd->ends_at ? $sponsoredAd->ends_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $sponsoredAd->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary">Update Ad</button>
            </form>
        </div>
    </div>
</div>
@endsection
