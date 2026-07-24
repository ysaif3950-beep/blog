# Blog_App - Agent Instructions

## Quick Commands

```bash
# Install & setup
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed

# Dev server (all services)
composer run dev

# Build assets
npm run build

# Tests
php artisan test --compact                    # all tests
php artisan test --compact --filter=TestName  # single test
php artisan test --compact tests/Feature/Api/V1/Auth/LoginTest.php  # single file

# Lint
vendor/bin/pint --dirty
```

## Architecture Notes

- **Laravel 12** with streamlined structure: `bootstrap/app.php` for middleware/exceptions/routes, `bootstrap/providers.php` for providers
- **API versioned at `/api/v1`** (routes/api.php), web routes at `routes/web.php`
- **Sanctum** for API auth (token-based), web uses session auth
- **Policies** in `app/Policies/` control authorization (PostPolicy, TagPolicy, UserPolicy)
- **API Resources** in `app/Http/Resources/Api/V1/` transform responses
- **Form Requests** in `app/Http/Requests/Api/V1/` for validation

## Key Conventions

- Controllers use `ApiResponse` trait (`success()`, `created()`, `updated()`, `paginated()`, etc.)
- Policies: `$this->authorizeResource(Model::class, 'param')` in controllers
- Images stored in `storage/app/public/uploads/`, accessed via `Storage::url()` with `default.png` fallback
- Arabic success messages in API responses
- Rate limits: `throttle:auth` (5/min), `throttle:authenticated` (60/min), `throttle:heavy` (custom)

## Testing

- Uses **PHPUnit** (not Pest) — `tests/Feature/` for feature, `tests/Unit/` for unit
- `ApiTestCase` base class provides `actingAsUser()`, `apiGet()`, `apiPost()`, etc.
- Factories in `database/factories/`, seeders in `database/seeders/`
- Run `php artisan test --compact --filter=TestName` after changes

## Key Files

| Purpose | Path |
|---------|------|
| API routes | `routes/api.php` |
| Web routes | `routes/web.php` |
| Middleware config | `bootstrap/app.php` |
| Providers | `bootstrap/providers.php` |
| API Controllers | `app/Http/Controllers/Api/V1/` |
| Web Controllers | `app/Http/Controllers/` |
| Models | `app/Models/` |
| Policies | `app/Policies/` |
| API Resources | `app/Http/Resources/Api/V1/` |
| Form Requests | `app/Http/Requests/Api/V1/` |

## Gotchas

- **Laravel 12**: middleware registered in `bootstrap/app.php`, not `app/Http/Kernel.php`
- **Pint**: run `vendor/bin/pint --dirty` before committing
- **Vite**: run `npm run build` or `npm run dev` if assets not loading
- **Sanctum tokens**: created per-device, refreshed via `/auth/refresh` with user-agent binding
- Some comments/messages are in Arabic