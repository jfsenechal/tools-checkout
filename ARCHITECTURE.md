# Architecture Documentation

This document explains the architectural decisions and patterns used in the Tool Checkout System, following Laravel Boost guidelines and modern Laravel best practices.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Design Patterns](#design-patterns)
3. [Directory Structure](#directory-structure)
4. [Data Flow](#data-flow)
5. [Best Practices Applied](#best-practices-applied)

## Architecture Overview

The application follows a **layered architecture** approach:

```
┌─────────────────────────────────────────────┐
│         Presentation Layer                   │
│  (Filament Admin + Scanner PWA)              │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         Application Layer                    │
│  (Controllers + Actions + DTOs)              │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         Domain Layer                         │
│  (Models + Business Logic)                   │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         Infrastructure Layer                 │
│  (Database + External Services)              │
└─────────────────────────────────────────────┘
```

## Design Patterns

### 1. Action Pattern (Laravel Boost)

**Purpose**: Encapsulate single business operations in dedicated classes.

**Location**: `app/Actions/`

**Example**: 
```php
class CheckoutToolAction
{
    public function execute(CheckoutData $data): Checkout
    {
        return DB::transaction(function () use ($data) {
            // Business logic here
        });
    }
}
```

**Why**: 
- Single Responsibility Principle
- Reusable across controllers, commands, jobs
- Easy to test in isolation
- Clear business operation boundaries

**Used in**:
- `CheckoutToolAction` - Handles tool checkout logic
- `ReturnToolAction` - Handles tool return logic

### 2. Data Transfer Object (DTO) Pattern

**Purpose**: Type-safe data transfer between layers.

**Location**: `app/DataTransferObjects/`

**Example**:
```php
readonly class CheckoutData
{
    public function __construct(
        public int $toolId,
        public int $workerId,
        public ?Carbon $checkedOutAt = null,
        // ... more properties
    ) {}
}
```

**Why**:
- Type safety
- Immutability (readonly)
- Clear contracts between layers
- Auto-completion in IDEs
- Prevents invalid data propagation

**Used in**:
- `CheckoutData` - Tool checkout information
- `ReturnData` - Tool return information

### 3. Service Layer Pattern

**Purpose**: Handle external integrations and complex business operations.

**Location**: `app/Services/`

**Example**:
```php
class QRCodeService
{
    public function generateForTool(Tool $tool): string
    {
        // QR code generation logic
    }
}
```

**Why**:
- Separation of concerns
- Easy to mock in tests
- Centralized logic for third-party integrations
- Can be swapped with different implementations

**Used in**:
- `QRCodeService` - QR code generation and management

### 4. Repository Pattern (Eloquent Models)

**Purpose**: Abstract data access layer.

**Location**: `app/Models/`

**Why**:
- Eloquent provides excellent repository-like interface
- Custom query scopes for complex queries
- Relationship definitions
- Attribute casting

**Scopes Used**:
```php
Tool::available()
Tool::checkedOut()
Checkout::active()
Checkout::overdue()
```

### 5. Resource Pattern (Filament)

**Purpose**: Admin interface generation with form/table definitions.

**Location**: `app/Filament/Resources/`

**Why**:
- Rapid admin panel development
- Consistent UI/UX
- Built-in CRUD operations
- Extensible with custom actions

## Directory Structure

```
tool-checkout-system/
├── app/
│   ├── Actions/              # Business operation classes
│   │   └── Checkout/
│   │       ├── CheckoutToolAction.php
│   │       └── ReturnToolAction.php
│   │
│   ├── DataTransferObjects/  # Type-safe data containers
│   │   ├── CheckoutData.php
│   │   └── ReturnData.php
│   │
│   ├── Models/               # Eloquent models
│   │   ├── Tool.php
│   │   ├── Worker.php
│   │   └── Checkout.php
│   │
│   ├── Services/             # External service integrations
│   │   └── QRCodeService.php
│   │
│   ├── Filament/             # Admin panel resources
│   │   └── Resources/
│   │       ├── ToolResource.php
│   │       ├── WorkerResource.php
│   │       └── CheckoutResource.php
│   │
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── ScannerController.php  # API endpoints
│
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/             # Sample data
│
├── resources/
│   └── views/
│       └── scanner.blade.php # PWA scanner interface
│
└── routes/
    ├── api.php              # API routes
    └── web.php              # Web routes
```

## Data Flow

### Checkout Flow

```
Scanner Client (PWA)
    ↓ (POST /api/scanner/checkout)
ScannerController::checkout()
    ↓ (validates request)
CheckoutData::fromRequest()
    ↓ (creates DTO)
CheckoutToolAction::execute()
    ↓ (business logic in transaction)
    ├── Verify tool availability
    ├── Verify worker status
    ├── Create Checkout record
    └── Update Tool status
    ↓
Return Checkout (with relationships)
    ↓
JSON Response to Client
```

### Return Flow

```
Scanner Client (PWA)
    ↓ (POST /api/scanner/return)
ScannerController::return()
    ↓ (validates request)
ReturnData::fromRequest()
    ↓ (creates DTO)
ReturnToolAction::execute()
    ↓ (business logic in transaction)
    ├── Find active checkout
    ├── Update return information
    └── Update tool status (based on condition)
    ↓
Return Checkout (with relationships)
    ↓
JSON Response to Client
```

## Best Practices Applied

### 1. Laravel Boost Principles

✅ **Actions for business logic** - Not in controllers
✅ **DTOs for data transfer** - Type-safe data flow
✅ **Service classes** - External integrations
✅ **Query scopes** - Reusable query logic
✅ **Form Requests** - Validation in dedicated classes

### 2. SOLID Principles

**Single Responsibility**
- Each action does one thing
- Services handle one concern
- Models represent one entity

**Open/Closed**
- Easy to extend without modifying core
- New checkout types can be added
- Custom tool categories

**Liskov Substitution**
- Service interfaces can be swapped
- Different QR code generators can be used

**Interface Segregation**
- Small, focused interfaces
- DTOs contain only needed data

**Dependency Inversion**
- Depend on abstractions (Actions, Services)
- Not on concrete implementations

### 3. Database Design

**Normalization**
- Tools, Workers, Checkouts in separate tables
- No data duplication
- Referential integrity with foreign keys

**Indexes**
- Status columns indexed for fast filtering
- Foreign keys indexed for joins
- Date columns indexed for range queries

**Soft Deletes**
- Maintain historical data
- Can restore accidentally deleted records
- Audit trail

### 4. Security

**Input Validation**
- Form requests validate all input
- DTOs ensure type safety
- SQL injection prevention (Eloquent)

**Authentication**
- Filament handles admin authentication
- API can be protected with Sanctum

**Authorization**
- Can add Filament policies for fine-grained control

### 5. Performance

**Eager Loading**
```php
Checkout::with(['tool', 'worker'])->get();
```
Prevents N+1 query problems

**Query Optimization**
- Indexes on frequently queried columns
- Scopes for complex queries
- Proper use of `select()` to limit fields

**Caching**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Testing Strategy

**Unit Tests**
- Test Actions in isolation
- Test DTO transformations
- Test Service methods

**Feature Tests**
- Test API endpoints
- Test checkout/return flows
- Test validations

**Example**:
```php
public function test_can_checkout_tool()
{
    $tool = Tool::factory()->create(['status' => 'available']);
    $worker = Worker::factory()->create(['status' => 'active']);
    
    $data = new CheckoutData(
        toolId: $tool->id,
        workerId: $worker->id
    );
    
    $checkout = app(CheckoutToolAction::class)->execute($data);
    
    $this->assertInstanceOf(Checkout::class, $checkout);
    $this->assertEquals('checked_out', $tool->fresh()->status);
}
```

## API Design

### RESTful Principles

```
POST   /api/scanner/scan       - Identify resource
GET    /api/scanner/workers    - List resources
POST   /api/scanner/checkout   - Create checkout
POST   /api/scanner/return     - Update checkout (return)
```

### JSON Response Format

**Success**:
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

**Error**:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

## Frontend Architecture (Scanner PWA)

### Alpine.js Component

**State Management**:
```javascript
{
  cameraActive: false,
  scannedTool: null,
  workers: [],
  // ... reactive state
}
```

**Methods**:
- Grouped by functionality
- Async operations clearly marked
- Error handling in every method

**API Communication**:
```javascript
async handleQRCode(qrData) {
    const response = await fetch('/api/scanner/scan', {
        method: 'POST',
        headers: { ... },
        body: JSON.stringify({ qr_data: qrData })
    });
    // Handle response
}
```

## Deployment Architecture

### Production Setup

```
Internet
    ↓
[Nginx/Apache/FrankenPHP]
    ↓
[PHP-FPM / FrankenPHP Worker]
    ↓
[Laravel Application]
    ↓
[MySQL Database]
```

### Scaling Options

**Horizontal Scaling**:
- Load balancer → Multiple app servers
- Shared database
- Redis for sessions/cache

**Vertical Scaling**:
- Increase server resources
- Database query optimization
- Caching layers

## Monitoring & Logging

### Activity Log

```php
use Spatie\Activitylog\Traits\LogsActivity;

class Tool extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status'])
            ->logOnlyDirty();
    }
}
```

Tracks:
- Tool status changes
- Worker modifications
- Checkout/return operations

### Application Logs

Located in `storage/logs/laravel.log`

Levels:
- Emergency, Alert, Critical
- Error, Warning, Notice
- Info, Debug

## Future Enhancements

### Potential Additions

1. **Email Notifications**
   - Overdue tool reminders
   - Weekly summaries
   - Return confirmations

2. **Advanced Reporting**
   - Usage analytics
   - Popular tools
   - Worker statistics
   - Cost tracking

3. **Maintenance Scheduling**
   - Scheduled maintenance
   - Maintenance history
   - Service reminders

4. **Multi-location Support**
   - Location hierarchy
   - Inter-location transfers
   - Location-specific workers

5. **Mobile Apps**
   - Native iOS/Android apps
   - Push notifications
   - Offline sync

## Conclusion

This architecture provides:
- ✅ Maintainability - Clear separation of concerns
- ✅ Testability - Isolated components
- ✅ Scalability - Can grow with needs
- ✅ Developer Experience - Modern patterns
- ✅ Type Safety - DTOs and strong typing

Following Laravel Boost guidelines ensures the codebase remains clean, organized, and easy to work with as the application grows.
