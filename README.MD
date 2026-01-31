# Tool Checkout Management System

A modern, full-featured tool checkout management system built with Laravel 12 and FilamentPHP 4. Designed for tracking tools loaned to workers with QR code scanning capabilities.

## Features

### Admin Panel (FilamentPHP 4)
- 📦 **Tool Management**: Complete CRUD operations for tools with categories, status tracking, and maintenance records
- 👷 **Worker Management**: Manage workers with badge numbers, departments, and status tracking
- 📋 **Checkout Tracking**: Full history of checkouts and returns with condition tracking
- 🔍 **Advanced Filters**: Search and filter by status, category, worker, date range
- 📊 **Reports & Analytics**: View checkout history, overdue items, usage statistics
- 📱 **QR Code Generation**: Automatic QR code generation for each tool
- 🔔 **Activity Logging**: Track all changes with Spatie Activity Log

### Scanner Client (PWA)
- 📷 **QR Code Scanning**: Real-time camera-based scanning using HTML5 and jsQR
- 📱 **Progressive Web App**: Install on any smartphone, works offline
- ⚡ **Quick Checkout/Return**: Scan → Select Worker → Done
- 👥 **Worker Search**: Fast worker lookup with autocomplete
- 🔄 **Real-time Status**: Instant tool availability and checkout status
- 💪 **Barcode Scanner Support**: Works with USB/Bluetooth barcode scanners

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **Admin Panel**: FilamentPHP 4
- **Database**: MySQL 8.0+ / PostgreSQL
- **QR Codes**: SimpleSoftwareIO/simple-qrcode
- **Activity Log**: Spatie Laravel Activity Log

### Frontend (Scanner)
- **Framework**: Alpine.js 3.x
- **Styling**: Tailwind CSS 3.x
- **QR Scanning**: jsQR library
- **PWA**: Service Worker with offline support

### Development Standards
- **Architecture**: Laravel Boost guidelines
- **Code Style**: PSR-12, Laravel Pint
- **Patterns**: Actions, DTOs, Service Classes
- **Testing**: PHPUnit, Pest (optional)

## Requirements

- **PHP**: 8.3 or higher
- **Composer**: 2.x
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx, Apache, or FrankenPHP
- **Node.js**: 18+ (for asset compilation, optional)
- **Extensions**: PDO, PDO_MySQL, mbstring, XML, GD, cURL, ZIP, BCMath

## Installation

### Quick Installation (Recommended)

1. **Clone the repository**
```bash
git clone <repository-url> tool-checkout-system
cd tool-checkout-system
```

2. **Run the installation script**
```bash
chmod +x install.sh
./install.sh
```

The script will:
- Check system requirements
- Install Composer dependencies
- Configure the environment
- Create the database
- Run migrations
- Generate QR codes storage
- Create an admin user
- Set up FilamentPHP

### Manual Installation

1. **Install dependencies**
```bash
composer install --optimize-autoloader
```

2. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Edit `.env` file**
```env
APP_NAME="Tool Checkout System"
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tool_checkout
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Database setup**
```bash
php artisan migrate
php artisan db:seed # Optional: seed sample data
```

5. **Storage setup**
```bash
php artisan storage:link
```

6. **Install Filament**
```bash
php artisan filament:install --panels
```

7. **Create admin user**
```bash
php artisan make:filament-user
```

## Deployment

### Option 1: FrankenPHP (Recommended for Modern Setup)

FrankenPHP is the modern Laravel application server with built-in HTTP/2, HTTP/3, and automatic HTTPS.

```bash
# Install FrankenPHP
curl -L https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp
chmod +x frankenphp
sudo mv frankenphp /usr/local/bin/

# Run the application
cd /path/to/tool-checkout-system
frankenphp php-server --domain your-domain.com
```

**Systemd Service for FrankenPHP:**

Create `/etc/systemd/system/tool-checkout.service`:

```ini
[Unit]
Description=Tool Checkout System
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/tool-checkout-system
ExecStart=/usr/local/bin/frankenphp php-server --listen :80
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable tool-checkout
sudo systemctl start tool-checkout
```

### Option 2: Traditional Nginx + PHP-FPM

**Nginx configuration** (`/etc/nginx/sites-available/tool-checkout`):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/tool-checkout-system/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/tool-checkout /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Option 3: Apache + mod_php

**Apache configuration** (`/etc/apache2/sites-available/tool-checkout.conf`):

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/tool-checkout-system/public

    <Directory /var/www/tool-checkout-system/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tool-checkout-error.log
    CustomLog ${APACHE_LOG_DIR}/tool-checkout-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite tool-checkout
sudo a2enmod rewrite
sudo systemctl reload apache2
```

## Configuration

### QR Code Settings

Configure QR code generation in `config/services.php`:

```php
'qr_code' => [
    'size' => 300,
    'margin' => 2,
    'format' => 'svg', // or 'png'
    'error_correction' => 'H',
],
```

### Scanner Settings

The scanner PWA can be customized in `resources/views/scanner.blade.php`. Key settings:

- **Scan interval**: Line 367 (`500ms` default)
- **Camera facing mode**: Line 348 (`'environment'` for rear camera)
- **Worker search limit**: Line 165 (`50` workers max)

## Usage

### Admin Panel

1. **Access the admin panel**: `http://your-domain.com/admin`
2. **Login** with the credentials created during installation
3. **Add Tools**: Navigate to Tools → Create Tool
4. **Generate QR Codes**: Use the "Generate QR" action for each tool
5. **Add Workers**: Navigate to Workers → Create Worker
6. **Manage Checkouts**: Navigate to Checkouts to see active loans

### Scanner Client

1. **Access the scanner**: `http://your-domain.com/scanner`
2. **Install as PWA**: Tap the "Add to Home Screen" option in your mobile browser
3. **Grant camera permission** when prompted
4. **Scan tool QR code**
5. **Select worker** from the list
6. **Confirm checkout** or **return**

### Printing QR Codes

After generating QR codes:

1. Go to Tools in admin panel
2. Click "View QR" for a tool
3. Right-click → Print or Save
4. Print on adhesive labels
5. Attach to tools

## API Endpoints

The scanner uses these API endpoints:

```
POST   /api/scanner/scan       - Scan and identify tool
GET    /api/scanner/workers    - Get list of active workers
POST   /api/scanner/checkout   - Checkout a tool
POST   /api/scanner/return     - Return a tool
```

### API Example

**Scan QR Code:**
```bash
curl -X POST http://your-domain.com/api/scanner/scan \
  -H "Content-Type: application/json" \
  -d '{"qr_data": "{\"type\":\"tool\",\"id\":1,\"code\":\"DRILL-001\"}"}'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tool": {
      "id": 1,
      "name": "DeWalt Drill",
      "code": "DRILL-001",
      "status": "available",
      "is_available": true
    },
    "current_checkout": null
  }
}
```

## Database Schema

### Tools Table
- `id`, `name`, `code` (unique), `qr_code`, `category`, `description`
- `status`: available, checked_out, maintenance, retired
- `location`, `purchase_price`, `purchase_date`
- `manufacturer`, `model`, `notes`
- `created_at`, `updated_at`, `deleted_at`

### Workers Table
- `id`, `name`, `badge_number` (unique), `email`, `phone`
- `department`, `position`
- `status`: active, inactive, suspended
- `notes`, `created_at`, `updated_at`, `deleted_at`

### Checkouts Table
- `id`, `tool_id`, `worker_id`
- `checked_out_at`, `expected_return_at`, `returned_at`
- `checked_out_by`, `returned_by` (user IDs)
- `condition_out`, `condition_in`: excellent, good, fair, poor
- `checkout_notes`, `return_notes`
- `is_overdue` (boolean)
- `created_at`, `updated_at`

## Customization

### Adding Custom Tool Categories

Edit `app/Filament/Resources/ToolResource.php`:

```php
Forms\Components\Select::make('category')
    ->options([
        'Power Tools' => 'Power Tools',
        'Hand Tools' => 'Hand Tools',
        'Your Category' => 'Your Category',
        // Add more categories
    ])
```

### Custom Worker Fields

Edit `app/Filament/Resources/WorkerResource.php` to add custom fields like employee number, shift, etc.

### Email Notifications

Add overdue notifications by creating a scheduled command:

```bash
php artisan make:command CheckOverdueTools
```

Then register in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tools:check-overdue')->daily();
}
```

## Troubleshooting

### QR Codes not generating
- Check storage permissions: `chmod -R 775 storage`
- Verify GD extension: `php -m | grep gd`
- Check storage link: `php artisan storage:link`

### Scanner not working
- Verify HTTPS (required for camera access)
- Check browser permissions for camera
- Test with different browsers
- Ensure `/api/scanner/*` routes are accessible

### Database connection issues
- Verify credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

### Performance issues
- Enable caching: `php artisan config:cache && php artisan route:cache`
- Use queue for QR generation: Configure queues in `.env`
- Add database indexes for large datasets

## Security

### Production Checklist

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure proper `APP_URL`
- [ ] Use strong `APP_KEY` (auto-generated)
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS if needed
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable rate limiting on API routes
- [ ] Regular database backups
- [ ] Keep Laravel and dependencies updated

### API Security

Add authentication for API routes if needed in `routes/api.php`:

```php
Route::middleware('auth:sanctum')->prefix('scanner')->group(function () {
    // Protected routes
});
```

## Maintenance

### Backup Database
```bash
php artisan backup:run # If using spatie/laravel-backup
# Or manual:
mysqldump -u username -p tool_checkout > backup.sql
```

### Update Application
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support & Contributing

### Reporting Issues
Please report issues with:
- PHP version
- Laravel version
- Browser (for scanner issues)
- Error messages
- Steps to reproduce

### Development

1. Fork the repository
2. Create a feature branch
3. Follow Laravel Boost guidelines
4. Write tests
5. Submit a pull request

## License

This project is open-sourced software licensed under the MIT license.

## Credits

Built with:
- [Laravel](https://laravel.com)
- [FilamentPHP](https://filamentphp.com)
- [Alpine.js](https://alpinejs.dev)
- [Tailwind CSS](https://tailwindcss.com)
- [jsQR](https://github.com/cozmo/jsQR)

---

**Need help?** Check the documentation in the `docs/` folder or open an issue.
