# Asset Management Guide

This project has been configured to use direct CSS and JavaScript files instead of Vite for asset compilation. This document explains how to manage assets in this setup.

## Directory Structure

- `resources/css/` - Source CSS files
- `resources/js/` - Source JavaScript files
- `public/css/` - Published CSS files (served directly to the browser)
- `public/js/` - Published JavaScript files (served directly to the browser)

## How Assets Are Loaded

Assets are loaded directly in the blade templates using the `asset()` helper:

```php
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<script src="{{ asset('js/app.js') }}"></script>
```

## Publishing Assets

When you make changes to files in the `resources/css/` or `resources/js/` directories, you need to publish them to the `public/` directory. Use the provided script:

```bash
php publish-assets.php
```

This script will copy all CSS and JS files from the resources directory to the public directory.

## Adding New Assets

1. Create your CSS or JS file in the appropriate resources directory
2. Reference it in your blade template using the `asset()` helper
3. Run the publish script to copy it to the public directory

Example:

```php
<!-- For a new file resources/css/custom.css -->
<link href="{{ asset('css/custom.css') }}" rel="stylesheet">
```

## Using Third-Party Libraries

For third-party libraries, you have two options:

1. **CDN Links** (recommended for popular libraries):
   ```php
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   ```

2. **Download and include in public directory**:
   - Download the library files
   - Place them in the appropriate public directory (e.g., `public/js/vendor/`)
   - Reference them in your blade templates:
   ```php
   <script src="{{ asset('js/vendor/library.min.js') }}"></script>
   ```

## Troubleshooting

If you're experiencing issues with assets not loading:

1. Make sure the files exist in the public directory
2. Check that the paths in your blade templates are correct
3. Clear your browser cache
4. Run `php artisan cache:clear` to clear Laravel's cache
5. Check browser console for any errors

## Benefits of This Approach

- Simpler setup without build tools
- Direct editing of CSS and JS files
- No need to run a development server for asset compilation
- Faster page loads in development
- Easier debugging (no source maps needed)
