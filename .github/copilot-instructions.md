# GitHub Copilot Instructions for Blog App

## Project Overview
This is a Laravel 12 blog application with a REST API, featuring user authentication, posts, tags, and image uploads. The API uses versioning (`/api/v1/`) and follows consistent response patterns.

## Architecture & Key Components

### API Structure
- **Versioned Routes**: All API endpoints prefixed with `/api/v1/`
- **Authentication**: Laravel Sanctum with Bearer tokens
- **Rate Limiting**: Custom throttle middleware (`auth`, `authenticated`, `heavy`)
- **Resources**: API responses use Laravel API Resources (`PostResource`, `UserResource`, `TagResource`)

### Core Models & Relationships
```php
User (hasMany) → Posts
Post (belongsTo) → User
Post (belongsToMany) → Tags
Tag (belongsToMany) → Posts
```

### Response Patterns
**Success Response:**
```json
{
  "success": true,
  "message": "Operation completed",
  "data": { /* resource data */ }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { /* validation errors */ }
}
```

## Essential Patterns

### Controllers
- Use `ApiResponse` trait for consistent responses
- Include `$this->authorizeResource(Model::class, 'param')` in constructors
- Handle file uploads with `Storage::disk('public')`
- Use eager loading: `Post::with(['user', 'tags'])`

### API Resources
- Extend `JsonResource`
- Use `whenLoaded()` for relationships
- Accessor example: `getImageUrlAttribute()` for image URLs

### Error Handling
- Use `ApiException` for custom errors
- Handler automatically formats common exceptions (ValidationException, AuthenticationException, etc.)
- Rate limiting returns 429 with "Too Many Requests"

### Authentication & Authorization
- Policies control access (PostPolicy: owner or admin can edit/delete)
- Sanctum middleware protects authenticated routes
- Password reset and refresh token endpoints available

### File Storage
- Images stored in `storage/app/public/uploads/`
- Use `Storage::url()` for public URLs
- Default fallback image: `uploads/default.png`

## Development Workflow

### Building & Running
```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Development server
php artisan serve
npm run dev

# Build for production
npm run build
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TestName

# Test with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```

## Key Files & Directories

### API Controllers
- `app/Http/Controllers/Api/V1/` - Versioned API controllers
- All controllers use `ApiResponse` trait

### Models
- `app/Models/Post.php` - Includes image URL accessor
- `app/Models/User.php` - Sanctum tokens, posts relationship
- `app/Models/Tag.php` - Many-to-many with posts

### Traits
- `app/Traits/ApiResponse.php` - Success/error response methods
- `app/Traits/ApiErrorResponse.php` - Alternative response format

### Policies
- `app/Policies/PostPolicy.php` - Owner/admin permissions

### Resources
- `app/Http/Resources/` - API response transformers

### Routes
- `routes/api.php` - Versioned API routes with middleware groups

### Tests
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests
- Use factories for test data

## Common Patterns

### Creating API Endpoints
1. Create Form Request: `php artisan make:request StorePostRequest`
2. Create Resource: `php artisan make:resource PostResource`
3. Add route to `routes/api.php` with middleware
4. Implement controller method using `ApiResponse` trait

### Image Upload
```php
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('uploads', 'public');
    $post->image = $path;
}
```

### Validation & Response
```php
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'description' => 'required|string'
]);

return $this->success($post, 'Post created successfully', 201);
```

### Authorization
```php
$this->authorize('update', $post); // Uses PostPolicy
```

## Dependencies
- **Laravel 12** with Sanctum authentication
- **Tailwind CSS v4** for styling
- **Vite** for asset bundling
- **PHPUnit** for testing
- **Pint** for code formatting

## Notes
- Some code comments are in Arabic
- API responses include Arabic success messages
- Rate limiting configured for auth (5/min), authenticated (60/min), heavy operations (custom)
- Image URLs generated via Storage facade with fallback to default image</content>
<parameter name="filePath">f:\xampp\htdocs\Blog_App\.github\copilot-instructions.md
