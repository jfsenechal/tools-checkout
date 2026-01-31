# Tool Checkout System - Complete Package

## 🎉 What You've Received

A complete, production-ready tool checkout management system built with:
- **Laravel 12** (latest version)
- **FilamentPHP 4** (latest version)
- **Laravel Boost guidelines** (best practices)

## 📦 Package Contents

### Core Application Files
```
tool-checkout-system/
├── 📱 Scanner PWA (Progressive Web App)
├── 🖥️  Admin Panel (FilamentPHP)
├── 🔧 Backend API (Laravel)
├── 📊 Database Structure
├── 🎯 QR Code System
└── 📚 Complete Documentation
```

### Documentation Files
1. **README.md** - Complete reference manual
2. **QUICKSTART.md** - 5-minute installation guide
3. **ARCHITECTURE.md** - Technical architecture details
4. **PROJECT_SUMMARY.md** - Feature overview
5. **install.sh** - Automated installation script

## 🚀 Quick Installation Guide

### Step 1: Upload to Your Server

```bash
# Upload the tool-checkout-system folder to your server
scp -r tool-checkout-system user@your-server:/var/www/

# SSH into your server
ssh user@your-server

# Navigate to directory
cd /var/www/tool-checkout-system
```

### Step 2: Run Installation

```bash
# Make installation script executable
chmod +x install.sh

# Run installation (will guide you through setup)
./install.sh
```

The script will:
- ✅ Check system requirements
- ✅ Install Composer dependencies
- ✅ Configure environment
- ✅ Set up database
- ✅ Run migrations
- ✅ Create admin user
- ✅ Set permissions

### Step 3: Configure Web Server

#### For FrankenPHP (Recommended - Modern)

```bash
# Install FrankenPHP
curl -L https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp
chmod +x frankenphp
sudo mv frankenphp /usr/local/bin/

# Run
cd /var/www/tool-checkout-system
frankenphp php-server --domain your-domain.com
```

#### For Nginx (Traditional)

Create `/etc/nginx/sites-available/tool-checkout`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/tool-checkout-system/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable:
```bash
sudo ln -s /etc/nginx/sites-available/tool-checkout /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 4: Access the System

1. **Admin Panel**: `http://your-domain.com/admin`
2. **Scanner**: `http://your-domain.com/scanner`

## 🔑 First Login

Use the credentials you created during installation.

## 📱 Setting Up the Scanner

### On Desktop
1. Open `http://your-domain.com/scanner`
2. Allow camera access
3. Start scanning tools

### On Mobile (Install as PWA)

**iPhone:**
1. Open in Safari
2. Tap Share button
3. "Add to Home Screen"

**Android:**
1. Open in Chrome
2. Tap menu (⋮)
3. "Add to Home Screen"

## 🎯 Quick Workflow

### 1. Add Tools
```
Admin Panel → Tools → Create Tool
↓
Fill in details (name, code, category)
↓
Click "Generate QR" action
↓
Print QR code and attach to tool
```

### 2. Add Workers
```
Admin Panel → Workers → Create Worker
↓
Fill in details (name, badge number)
↓
Save
```

### 3. Checkout Tool
```
Open Scanner → Allow Camera
↓
Scan Tool QR Code
↓
Select Worker
↓
Confirm Checkout
```

### 4. Return Tool
```
Open Scanner
↓
Scan Tool QR Code
↓
Click "Return Tool"
↓
Confirm Return
```

## 📊 Sample Data (Optional)

Want to test with sample data?

```bash
php artisan db:seed
```

Creates:
- 10 sample tools with QR codes
- 5 sample workers
- Example checkouts (active and returned)

## 🛠️ Configuration

### Environment (.env)

Key settings to configure:

```env
# Application
APP_NAME="Your Company Tool Checkout"
APP_URL=https://your-domain.com

# Database
DB_DATABASE=tool_checkout
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail (for future notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

### Customize Categories

Edit `app/Filament/Resources/ToolResource.php`, line 42:

```php
Forms\Components\Select::make('category')
    ->options([
        'Power Tools' => 'Power Tools',
        'Hand Tools' => 'Hand Tools',
        'Your Category' => 'Your Category', // Add here
    ])
```

## 🔒 Production Checklist

Before going live:

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set proper `APP_URL` in `.env`
- [ ] Enable HTTPS (required for camera)
- [ ] Run optimization commands:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Set up database backups
- [ ] Configure proper file permissions
- [ ] Test scanner on production URL

## 📝 Common Tasks

### Create New Admin User
```bash
php artisan make:filament-user
```

### Regenerate QR Codes
```bash
php artisan tinker
>>> app(\App\Services\QRCodeService::class)->generateBatch(\App\Models\Tool::pluck('id')->toArray())
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Backup Database
```bash
mysqldump -u username -p tool_checkout > backup.sql
```

## 🆘 Troubleshooting

### Camera Not Working
- Ensure HTTPS is enabled (camera requires secure context)
- Check browser permissions
- Try different browser

### QR Codes Not Generating
```bash
# Check storage permissions
chmod -R 775 storage
sudo chown -R www-data:www-data storage

# Recreate storage link
php artisan storage:link
```

### Database Connection Error
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check MySQL is running
sudo systemctl status mysql
```

## 📚 Additional Resources

### Documentation Files
- **README.md** - Full documentation (installation, usage, API)
- **QUICKSTART.md** - Fast start guide
- **ARCHITECTURE.md** - Technical architecture and patterns
- **PROJECT_SUMMARY.md** - Feature overview

### Code Structure
```
Key Directories:
- app/Actions/          → Business logic
- app/Models/           → Database models
- app/Services/         → External services
- app/Filament/         → Admin panel
- resources/views/      → Scanner interface
- database/migrations/  → Database schema
```

## 🌟 Features Overview

### Admin Panel
- ✅ Tool management with QR generation
- ✅ Worker management
- ✅ Checkout history
- ✅ Advanced filtering
- ✅ Export capabilities
- ✅ Activity logging

### Scanner
- ✅ QR code scanning
- ✅ Worker quick-select
- ✅ Quick checkout/return
- ✅ Offline support
- ✅ Mobile PWA

### System
- ✅ RESTful API
- ✅ Type-safe architecture
- ✅ Activity logging
- ✅ Soft deletes
- ✅ Performance optimized

## 🔄 Updates

To update the application later:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

## 💡 Pro Tips

1. **Print QR codes on durable labels** - Weatherproof labels work best
2. **Backup regularly** - Automate database backups
3. **Monitor logs** - Check `storage/logs/laravel.log`
4. **Use categories** - Organize tools by type for easy filtering
5. **Set expected return dates** - Track overdue items
6. **Add worker departments** - Better reporting and organization

## 🎓 Learning Resources

- [Laravel Documentation](https://laravel.com/docs)
- [FilamentPHP Documentation](https://filamentphp.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev)

## 📧 Support

For issues or questions:
1. Check QUICKSTART.md for common solutions
2. Review README.md troubleshooting section
3. Check ARCHITECTURE.md for technical details

## 🎉 You're All Set!

Your tool checkout system is ready to use. Start by:
1. Adding your tools
2. Generating QR codes
3. Adding workers
4. Testing the scanner

**Happy tracking!** 🚀

---

**Version**: 1.0.0
**Built with**: Laravel 12, FilamentPHP 4, Alpine.js
**License**: MIT
