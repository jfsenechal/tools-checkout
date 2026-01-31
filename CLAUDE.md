# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Tool Checkout System - A Laravel 12 application with FilamentPHP 4 admin panel for tracking tools loaned to workers. Includes a PWA scanner client for QR code-based checkouts/returns.

## Tech Stack

- **PHP 8.4**, **Laravel 12**, **Filament 4**, **Livewire 3**, **Tailwind CSS 4**
- **Pest 4** for testing, **Pint** for code formatting
- **Spatie Activity Log** for audit trails
- **SimpleSoftwareIO QR Code** for QR generation

## Commands

```bash
# Development
composer run dev              # Start server, queue, logs, and Vite concurrently
npm run dev                   # Vite dev server only
npm run build                 # Build assets for production

# Testing
php artisan test --compact                    # Run all tests
php artisan test --compact --filter=testName  # Run specific test

# Code formatting
vendor/bin/pint --dirty       # Format changed files (run before commits)

# Database
php artisan migrate           # Run migrations
php artisan db:seed           # Seed sample data

# Create new files (always use --no-interaction)
php artisan make:test --pest {name}           # Feature test
php artisan make:test --pest --unit {name}    # Unit test
php artisan make:model {name} -mfs            # Model with migration, factory, seeder
```

## Architecture

### Layered Structure

```
Presentation → Filament Resources + Scanner PWA (Alpine.js)
Application  → Controllers + Actions + DTOs
Domain       → Models with scopes and relationships
```

### Key Patterns

**Actions** (`app/Actions/`) - Single-purpose business operations wrapped in DB transactions:
- `CheckoutToolAction` - Validates tool availability, worker status, creates checkout
- `ReturnToolAction` - Updates checkout, changes tool status based on condition

**DTOs** (`app/DataTransferObjects/`) - Readonly classes with `fromRequest()` factory:
- `CheckoutData`, `ReturnData` - Type-safe data transfer between layers

**Services** (`app/Services/`) - External integrations:
- `QRCodeService` - QR code generation and batch processing

### Models

| Model | Key Scopes | Relationships |
|-------|-----------|---------------|
| `Tool` | `available()`, `checkedOut()`, `byCategory()` | `checkouts`, `currentCheckout` |
| `Worker` | `active()` | `checkouts` |
| `Checkout` | `active()`, `overdue()` | `tool`, `worker` |

### Filament Resources

Located in `app/Filament/Resources/`:
- `ToolResource` - Tool CRUD with QR code actions
- `WorkerResource` - Worker management
- `CheckoutResource` - Checkout history and tracking

### API Endpoints

Scanner PWA uses these routes (`routes/api.php`):
- `POST /api/scanner/scan` - Identify tool from QR data
- `GET /api/scanner/workers` - List active workers
- `POST /api/scanner/checkout` - Create checkout
- `POST /api/scanner/return` - Return tool

### Frontend

- Admin: `/admin` (Filament panel)
- Scanner PWA: `/scanner` (`resources/views/scanner.blade.php`, Alpine.js)

## Code Conventions

### PHP Style

- Use PHP 8 constructor property promotion
- Always declare return types and parameter types
- Prefer `Model::query()` over `DB::` facade
- Use eager loading to prevent N+1 queries
- Wrap business logic in Actions, not controllers
- Use Form Request classes for validation

### Filament v4 Namespaces

- Form fields: `Filament\Forms\Components\`
- Layout (Section, Grid): `Filament\Schemas\Components\`
- Schema utilities (Get, Set): `Filament\Schemas\Components\Utilities\`
- Actions: `Filament\Actions\`
- Icons: `Filament\Support\Icons\Heroicon` enum

### Testing

- Use factories with states for model creation
- Feature tests for API endpoints and flows
- Authenticate before testing Filament resources
- Use `livewire()` helper for Filament component tests

## Project Structure

```
app/
├── Actions/Checkout/           # Business logic
├── DataTransferObjects/        # Type-safe DTOs
├── Services/                   # External integrations
├── Models/                     # Eloquent models
├── Filament/Resources/         # Admin panel
└── Http/Controllers/Api/       # Scanner API
database/
├── migrations/                 # Schema
└── factories/                  # Test factories (need Tool, Worker, Checkout)
resources/views/
└── scanner.blade.php           # PWA interface
```

## Status Values

**Tool status**: `available`, `checked_out`, `maintenance`, `retired`
**Worker status**: `active`, `inactive`, `suspended`
**Condition**: `excellent`, `good`, `fair`, `poor`
