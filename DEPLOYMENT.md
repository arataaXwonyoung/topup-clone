# 🚀 Takapedia Deployment Guide

Panduan lengkap untuk deploy aplikasi Takapedia ke production server.

## 📋 Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04+ atau CentOS 8+
- **CPU**: Minimal 2 core, Recommended 4+ core  
- **RAM**: Minimal 4GB, Recommended 8GB+
- **Storage**: Minimal 50GB SSD
- **Bandwidth**: Unmetered atau minimal 100GB/bulan

### Software Stack
- **Web Server**: Nginx 1.18+
- **PHP**: 8.1+ dengan extensions yang diperlukan
- **Database**: MySQL 8.0+ atau PostgreSQL 13+
- **Cache**: Redis 6.0+
- **Queue**: Redis atau Supervisor
- **SSL**: Let's Encrypt (Certbot)

## 🔧 Step 1: Server Setup

### 1.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install curl wget git unzip software-properties-common -y
```

### 1.2 Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 1.3 Install PHP 8.2
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-common php8.2-mysql php8.2-xml php8.2-curl php8.2-gd php8.2-imagick php8.2-cli php8.2-dev php8.2-imap php8.2-mbstring php8.2-opcache php8.2-soap php8.2-zip php8.2-redis php8.2-intl -y
```

### 1.4 Install MySQL 8.0
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE takapedia_production;
CREATE USER 'takapedia_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON takapedia_production.* TO 'takapedia_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.5 Install Redis
```bash
sudo apt install redis-server -y
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### 1.6 Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 1.7 Install Node.js & NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## 📁 Step 2: Application Deployment

### 2.1 Create Application Directory
```bash
sudo mkdir -p /var/www/takapedia
sudo chown -R $USER:$USER /var/www/takapedia
cd /var/www/takapedia
```

### 2.2 Clone Repository
```bash
git clone https://github.com/yourusername/takapedia-clone.git .
```

### 2.3 Install Dependencies
```bash
# PHP Dependencies
composer install --optimize-autoloader --no-dev

# Node.js Dependencies
npm install
npm run build
```

### 2.4 Environment Configuration
```bash
# Copy production environment
cp .env.production.example .env

# Generate application key
php artisan key:generate

# Edit environment variables
nano .env
```

**Important Environment Variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=127.0.0.1
DB_DATABASE=takapedia_production
DB_USERNAME=takapedia_user
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Payment Gateway (PRODUCTION KEYS)
MIDTRANS_SERVER_KEY=Mid-server-YOUR_PRODUCTION_KEY
MIDTRANS_CLIENT_KEY=Mid-client-YOUR_PRODUCTION_KEY
MIDTRANS_IS_PRODUCTION=true
```

### 2.5 Database Migration
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force
```

### 2.6 Storage & Cache Optimization
```bash
# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct permissions
sudo chown -R www-data:www-data /var/www/takapedia
sudo chmod -R 755 /var/www/takapedia
sudo chmod -R 775 /var/www/takapedia/storage
sudo chmod -R 775 /var/www/takapedia/bootstrap/cache
```

## 🌐 Step 3: Web Server Configuration

### 3.1 Nginx Virtual Host
```bash
sudo nano /etc/nginx/sites-available/takapedia
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/takapedia/public;
    index index.php index.html index.htm;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Webhook endpoints (no rate limiting)
    location /webhooks/ {
        try_files $uri $uri/ /index.php?$query_string;
        access_log /var/log/nginx/takapedia_webhooks.log;
    }

    # API endpoints (rate limited)
    location /api/ {
        limit_req zone=api burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Admin panel (additional security)
    location /admin {
        # IP whitelist (optional)
        # allow 123.123.123.123;
        # deny all;
        
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
        access_log off;
    }

    # Block sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ ^/(\.env|composer\.(json|lock)|package\.json|artisan) {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

### 3.2 Enable Site
```bash
sudo ln -s /etc/nginx/sites-available/takapedia /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 🔒 Step 4: SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL Certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 2 * * * /usr/bin/certbot renew --quiet
```

## 🔄 Step 5: Queue Worker Setup

### 5.1 Install Supervisor
```bash
sudo apt install supervisor -y
```

### 5.2 Create Worker Configuration
```bash
sudo nano /etc/supervisor/conf.d/takapedia-worker.conf
```

```ini
[program:takapedia-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/takapedia/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/takapedia/storage/logs/worker.log
stopwaitsecs=3600
```

### 5.3 Start Workers
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start takapedia-worker:*
```

## 📊 Step 6: Monitoring & Logging

### 6.1 Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/takapedia
```

```
/var/www/takapedia/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### 6.2 System Monitoring Script
```bash
sudo nano /usr/local/bin/takapedia-monitor.sh
```

```bash
#!/bin/bash

# Health check script
URL="https://yourdomain.com/health"
LOG_FILE="/var/log/takapedia-monitor.log"

if ! curl -s -f $URL > /dev/null; then
    echo "$(date): Website is DOWN" >> $LOG_FILE
    # Send alert notification
    # TODO: Integrate with Slack/Telegram/Email
else
    echo "$(date): Website is UP" >> $LOG_FILE
fi

# Check queue workers
if ! supervisorctl status takapedia-worker:* | grep RUNNING > /dev/null; then
    echo "$(date): Queue workers are DOWN" >> $LOG_FILE
    sudo supervisorctl restart takapedia-worker:*
fi
```

```bash
chmod +x /usr/local/bin/takapedia-monitor.sh

# Add to crontab (check every 5 minutes)
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/takapedia-monitor.sh
```

## 🔄 Step 7: Backup Strategy

### 7.1 Database Backup Script
```bash
sudo nano /usr/local/bin/backup-database.sh
```

```bash
#!/bin/bash

# Database backup script
DB_NAME="takapedia_production"
DB_USER="takapedia_user"
DB_PASS="strong_password_here"
BACKUP_DIR="/var/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/takapedia_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "takapedia_*.sql.gz" -mtime +7 -delete

echo "Backup completed: takapedia_$DATE.sql.gz"
```

```bash
chmod +x /usr/local/bin/backup-database.sh

# Daily backup at 2 AM
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-database.sh
```

## 🚀 Step 8: Performance Optimization

### 8.1 PHP-FPM Tuning
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
; Adjust based on your server resources
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 1000

; Memory limit
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
```

### 8.2 Redis Optimization  
```bash
sudo nano /etc/redis/redis.conf
```

```
# Memory optimization
maxmemory 1gb
maxmemory-policy allkeys-lru

# Persistence (for sessions)
save 900 1
save 300 10
save 60 10000
```

### 8.3 MySQL Optimization
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
# Performance tuning
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
query_cache_size = 128M
max_connections = 200

# Security
bind-address = 127.0.0.1
```

## 🔒 Step 9: Security Hardening

### 9.1 Firewall Setup
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 9.2 Fail2Ban
```bash
sudo apt install fail2ban -y
sudo nano /etc/fail2ban/jail.local
```

```ini
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 3600

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
bantime = 600
```

## 🎯 Step 10: Go Live Checklist

### Before Launch:
- [ ] Domain DNS pointed to server
- [ ] SSL certificate installed and working
- [ ] All environment variables configured
- [ ] Database migrated and seeded
- [ ] Payment gateway in production mode
- [ ] Queue workers running
- [ ] Backup system configured
- [ ] Monitoring setup
- [ ] Error tracking (Sentry) configured

### Test Real Transactions:
```bash
# Test payment with small amount
# Test gamification system
# Test webhook endpoints
# Test account verification
# Test order fulfillment
```

### Launch Commands:
```bash
cd /var/www/takapedia

# Final cache clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart all services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart redis-server
sudo supervisorctl restart all

# Monitor logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/access.log
```

## 📱 Step 11: Post-Launch Monitoring

### Key Metrics to Monitor:
- **Response Time**: < 200ms average
- **Uptime**: 99.9%+ target
- **Queue Processing**: < 30 seconds average
- **Database Performance**: Query time < 100ms
- **Memory Usage**: < 80% of available RAM
- **Disk Space**: < 80% usage

### Daily Tasks:
```bash
# Check system health
php artisan health:check

# Monitor failed jobs
php artisan queue:failed

# Check logs for errors
grep ERROR storage/logs/laravel.log

# Verify backups
ls -la /var/backups/mysql/
```

---

## 🎉 Congratulations!

Your Takapedia application is now live with:
✅ Real payment processing  
✅ Automated game top-up  
✅ Full gamification system  
✅ Production-ready infrastructure  
✅ Security & monitoring  

**Support URLs:**
- Website: `https://yourdomain.com`
- Admin: `https://yourdomain.com/admin`  
- API: `https://yourdomain.com/api`
- Webhooks: `https://yourdomain.com/webhooks/midtrans`

Ready untuk menerima transaksi real dari user! 💰🚀