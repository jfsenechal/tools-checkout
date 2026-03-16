# Quick Start Guide

Get your Tool Checkout System up and running in minutes!

## Prerequisites Check

Before starting, ensure you have:
- ✅ PHP 8.3 or higher
- ✅ Composer installed
- ✅ MySQL 8.0+ or PostgreSQL 13+
- ✅ Web server (Nginx, Apache, or FrankenPHP)
- ✅ Basic Linux command line knowledge

## 5-Minute Installation

### Step 1: Clone & Install

```bash
# Clone the repository
git clone <repository-url> tool-checkout-system
cd tool-checkout-system

# Run automated installation
chmod +x install.sh
./install.sh
```

The script will guide you through:
1. Checking system requirements
2. Installing dependencies
3. Database configuration
4. Creating admin user

### Step 2: Start the Server

**Option A: FrankenPHP (Recommended)**
```bash
frankenphp php-server --listen :8000
```

**Option B: PHP Built-in Server (Development)**
```bash
php artisan serve
```

**Option C: Traditional (Nginx/Apache)**
See README.md for full configuration

### Step 3: Access the Application

1. **Admin Panel**: http://localhost:8000/admin
2. **Scanner Client**: http://localhost:8000/scanner

Login with the credentials you created during installation.

## First Steps

### 1. Add Your First Tool

1. Go to Admin Panel → Tools → Create Tool
2. Fill in the details:
   - **Name**: "DeWalt Drill" 
   - **Code**: "DRILL-001" (unique identifier)
   - **Category**: Select or type "Power Tools"
   - **Status**: "Available"
3. Click "Create"
4. Click "Generate QR" action to create QR code
5. Click "View QR" to see/print the code

### 2. Add Workers

1. Go to Admin Panel → Workers → Create Worker
2. Fill in the details:
   - **Name**: Worker's full name
   - **Badge Number**: Unique ID (e.g., "EMP001")
   - **Department**: Their department
   - **Status**: "Active"
3. Click "Create"

### 3. Test the Scanner

**On Desktop:**
1. Open http://localhost:8000/scanner in Chrome/Firefox
2. Grant camera permission
3. Point camera at the QR code you generated
4. Should automatically recognize the tool

**On Mobile:**
1. Open the scanner URL on your phone
2. Tap "Add to Home Screen" to install as PWA
3. Open the app
4. Grant camera permission
5. Scan a tool QR code

### 4. Perform a Checkout

1. Scan the tool QR code
2. Tool information appears
3. Tap "Checkout Tool"
4. Search and select worker
5. Confirm checkout

Tool is now checked out! You can see it in Admin Panel → Checkouts.

### 5. Return a Tool

1. Scan the checked-out tool
2. Shows current checkout info
3. Tap "Return Tool"
4. Confirm return

Tool is now available again!

## Sample Data (Optional)

Want to test with sample data?

```bash
php artisan db:seed
```

This creates:
- 10 sample tools across different categories
- 5 sample workers
- Sample checkouts (some active, some returned, one overdue)
- QR codes for all tools

## Common Tasks

### Generate QR Codes for All Tools

```bash
php artisan tinker
>>> app(\App\Services\QRCodeService::class)->generateBatch(\App\Models\Tool::pluck('id')->toArray())
```

### Create Additional Admin User

```bash
php artisan make:filament-user
```

### Export Tools List

Go to Admin Panel → Tools → Select tools → Export

### View Reports

Admin Panel → Checkouts → Filter by:
- Active only
- Overdue
- By worker
- By date range

## Mobile Setup

### Install Scanner as PWA on iPhone

1. Open scanner in Safari
2. Tap Share button
3. Tap "Add to Home Screen"
4. Tap "Add"

### Install Scanner as PWA on Android

1. Open scanner in Chrome
2. Tap menu (⋮)
3. Tap "Add to Home Screen"
4. Tap "Add"

### Use with Barcode Scanner

The scanner also works with USB/Bluetooth barcode scanners:
1. Connect scanner to device
2. Open scanner web interface
3. Scanner will automatically detect QR code scans
4. No need to use camera

## Troubleshooting

### QR Codes Not Showing

```bash
# Create storage link
php artisan storage:link

# Check permissions
chmod -R 775 storage
sudo chown -R www-data:www-data storage
```

### Camera Not Working

- Requires HTTPS in production (not localhost)
- Check browser permissions
- Try different browser
- Ensure camera is not used by another app

### Database Connection Failed

```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
php artisan tinker
>>> DB::connection()->getPdo()
```

### Can't Login to Admin

```bash
# Reset admin password
php artisan tinker
>>> $user = App\Models\User::first()
>>> $user->password = bcrypt('newpassword')
>>> $user->save()
```

## Next Steps

1. **Customize Categories**: Edit `app/Filament/Resources/ToolResource.php`
2. **Add Logo**: Replace logo in FilamentPHP settings
3. **Setup Email**: Configure MAIL settings in `.env`
4. **Enable Notifications**: Setup for overdue tools
5. **Backup Database**: Setup automated backups

## Production Deployment

When you're ready to deploy to production:

1. **Set Environment**:
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Enable HTTPS**: Required for camera access

3. **Optimize**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Setup Backups**: Configure regular database backups

5. **Monitor Logs**: Check `storage/logs/laravel.log`

## Get Help

- **Documentation**: See README.md for full documentation
- **Common Issues**: Check README.md troubleshooting section
- **Report Bug**: Open an issue with details

## Configuration Tips

### Change Scanner Colors

Edit `resources/views/scanner.blade.php` Tailwind classes:
- Primary color: `bg-blue-600` → `bg-green-600`
- Success: `bg-green-500`
- Error: `bg-red-500`

### Adjust Scan Speed

Edit line 367 in `resources/views/scanner.blade.php`:
```javascript
this.scanInterval = setInterval(() => {
    this.scan();
}, 500); // Change 500 to 1000 for slower scanning
```

### Change QR Code Size

Edit `app/Services/QRCodeService.php`:
```php
$qrCode = QrCode::format('svg')
    ->size(300)  // Change size here
    ->margin(2)
```

---

**Ready to go?** Start by adding your first tool and worker, then test the scanner! 🚀
