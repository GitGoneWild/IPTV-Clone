# HomelabTV - Complete Deployment Guide

This comprehensive guide covers all aspects of deploying HomelabTV from development to production environments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start with Docker](#quick-start-with-docker)
- [Manual Installation](#manual-installation)
- [Production Deployment](#production-deployment)
- [Queue Workers & Horizon](#queue-workers--horizon)
- [FFmpeg & Transcoding](#ffmpeg--transcoding)
- [Load Balancing](#load-balancing)
- [Monitoring & Maintenance](#monitoring--maintenance)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### System Requirements

**Minimum:**
- CPU: 2 cores
- RAM: 4GB
- Storage: 20GB
- OS: Ubuntu 20.04+ / Debian 11+ / CentOS 8+

**Recommended for Production:**
- CPU: 4+ cores
- RAM: 8GB+
- Storage: 100GB+ SSD
- OS: Ubuntu 22.04 LTS

### Software Requirements

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.6+ or PostgreSQL 13+
- Redis 6.0+
- Node.js 18+ & NPM (for frontend assets)
- FFmpeg 4.4+ (for transcoding)
- Nginx or Apache
- Supervisor (for queue workers)

---

## Quick Start with Docker

### 1. Clone and Configure

```bash
git clone https://github.com/GitGoneWild/IPTV-Clone.git homelabtv
cd homelabtv
cp .env.example .env
```

### 2. Update Environment Variables

Edit `.env` and update these critical settings:

```env
APP_NAME=HomelabTV
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=homelabtv
DB_USERNAME=homelabtv
DB_PASSWORD=your_secure_password

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Horizon
HORIZON_PATH=admin/horizon
HORIZON_NAME=HomelabTV
```

### 3. Start Services

```bash
docker-compose up -d
```

### 4. Initialize Application

```bash
# Install dependencies
docker-compose exec app composer install --optimize-autoloader --no-dev

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database with defaults
docker-compose exec app php artisan db:seed --class=RolePermissionSeeder
docker-compose exec app php artisan db:seed --class=TranscodeProfileSeeder

# Create admin user
docker-compose exec app php artisan db:seed --class=TestAdminSeeder

# Link storage
docker-compose exec app php artisan storage:link

# Start Horizon (queue worker)
docker-compose exec app php artisan horizon
```

### 5. Access Application

- Frontend: http://localhost:8080
- Admin Panel: http://localhost:8080/admin
- Horizon Dashboard: http://localhost:8080/admin/horizon

**Default Admin Credentials:**
- Email: admin@homelabtv.local
- Password: admin123 (⚠️ **CHANGE IMMEDIATELY**)

---

## Manual Installation

### 1. Install System Dependencies

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install -y php8.2 php8.2-{cli,fpm,mysql,pgsql,redis,mbstring,xml,curl,zip,gd,bcmath,intl} \
    mysql-server redis-server nginx supervisor ffmpeg composer nodejs npm
```

**CentOS/RHEL:**
```bash
sudo dnf install -y php php-{cli,fpm,mysqlnd,redis,mbstring,xml,curl,zip,gd,bcmath,intl,json} \
    mysql-server redis nginx supervisor ffmpeg composer nodejs npm
```

### 2. Configure Database

**MySQL:**
```bash
sudo mysql -e "CREATE DATABASE homelabtv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'homelabtv'@'localhost' IDENTIFIED BY 'your_secure_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON homelabtv.* TO 'homelabtv'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 3. Clone and Setup Application

```bash
cd /var/www
git clone https://github.com/GitGoneWild/IPTV-Clone.git homelabtv
cd homelabtv

# Set permissions
sudo chown -R www-data:www-data /var/www/homelabtv
sudo chmod -R 755 /var/www/homelabtv/storage
sudo chmod -R 755 /var/www/homelabtv/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment

Edit `/var/www/homelabtv/.env`:

```env
APP_NAME=HomelabTV
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=homelabtv
DB_USERNAME=homelabtv
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
CACHE_STORE=redis
QUEUE_CONNECTION=redis

HORIZON_PATH=admin/horizon
HORIZON_NAME=HomelabTV
```

### 5. Run Migrations and Seeders

```bash
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=TranscodeProfileSeeder
php artisan storage:link
php artisan optimize
```

### 6. Configure Web Server

**Nginx Configuration** (`/etc/nginx/sites-available/homelabtv`):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/homelabtv/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/homelabtv /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Production Deployment

### 1. Enable HTTPS with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### 2. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Setup Scheduled Tasks

Add to crontab (`crontab -e`):

```cron
* * * * * cd /var/www/homelabtv && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Configure PHP for Production

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

---

## Queue Workers & Horizon

### Why Horizon?

Laravel Horizon provides a beautiful dashboard and code-driven configuration for Redis-powered queues, including:
- Real-time queue monitoring
- Job metrics and throughput graphs
- Failed job management
- Worker load balancing

### Install and Configure

Horizon is already included in the project. Configure queue workers:

**Supervisor Configuration** (`/etc/supervisor/conf.d/homelabtv-horizon.conf`):

```ini
[program:homelabtv-horizon]
process_name=%(program_name)s
command=php /var/www/homelabtv/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/homelabtv/storage/logs/horizon.log
stopwaitsecs=3600
```

Start Horizon:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start homelabtv-horizon
```

### Queue Structure

HomelabTV uses multiple queues for different tasks:

- **default**: General background jobs
- **epg**: EPG imports and updates
- **imports**: Large file imports (XMLTV, M3U)
- **streams**: Stream health checks
- **health-checks**: System health monitoring

### Monitoring Horizon

Access Horizon dashboard at: `https://your-domain.com/admin/horizon`

Only admin users can access Horizon (configured in `HorizonServiceProvider`).

---

## FFmpeg & Transcoding

### Install FFmpeg

**Ubuntu/Debian:**
```bash
sudo apt install ffmpeg
```

**From Source (latest version):**
```bash
sudo apt install -y build-essential pkg-config yasm libx264-dev libx265-dev libvpx-dev libfdk-aac-dev libmp3lame-dev libopus-dev
cd /tmp
wget https://ffmpeg.org/releases/ffmpeg-6.0.tar.xz
tar xvf ffmpeg-6.0.tar.xz
cd ffmpeg-6.0
./configure --enable-gpl --enable-libx264 --enable-libx265 --enable-libfdk-aac --enable-libmp3lame --enable-nonfree
make -j$(nproc)
sudo make install
```

### Configure Transcode Profiles

1. Login to admin panel
2. Navigate to **Transcode Profiles**
3. Use default profiles or create custom ones

**Default Profiles Available:**
- Original (No Transcode)
- 1080p H.264 HLS
- 720p H.264 HLS
- 480p H.264 HLS
- 1080p H.265 HLS
- MPEG-TS Direct

### Transcode Profile Settings

Each profile includes:
- **Video Codec**: libx264, libx265, copy
- **Video Bitrate**: Target bitrate (e.g., 2000k)
- **Resolution**: Width x Height
- **Frame Rate**: Target FPS
- **Preset**: Encoding speed/quality trade-off
- **Audio Codec**: aac, mp3, copy
- **Audio Settings**: Bitrate, channels, sample rate
- **Container**: mpegts, hls, mp4

### Usage

Transcode profiles can be assigned to streams for:
- Adaptive bitrate streaming
- Format conversion
- Bandwidth optimization
- Multi-device compatibility

---

## Load Balancing

### Architecture

HomelabTV supports multi-server load balancing:

1. **Main Server**: Runs the application
2. **Load Balancer Nodes**: Distribute stream traffic
3. **Redis**: Centralizes state and queue management

### Setup Load Balancer Node

See `/docs/LOAD_BALANCER_DEPLOYMENT.md` for detailed instructions.

**Quick Setup:**

```bash
# On load balancer node
docker pull ghcr.io/gitgonewild/homelabtv-loadbalancer:latest
docker run -d \
  -e API_URL=https://main-server.com \
  -e API_KEY=your_api_key \
  -e REGION=us-east \
  -p 8080:8080 \
  ghcr.io/gitgonewild/homelabtv-loadbalancer:latest
```

### Benefits

- Geographic distribution
- Automatic failover
- Health monitoring
- Traffic distribution based on:
  - Server capacity
  - Geographic location
  - Current load
  - Health status

---

## Monitoring & Maintenance

### Health Checks

Run system health check:

```bash
php artisan homelabtv:health-check
```

Checks:
- ✅ Database connectivity
- ✅ Redis connectivity
- ✅ Storage permissions
- ✅ EPG directory
- ✅ Configuration validity

### Logs

**Application Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Horizon Logs:**
```bash
tail -f storage/logs/horizon.log
```

**Nginx Access Logs:**
```bash
tail -f /var/log/nginx/access.log
```

**Nginx Error Logs:**
```bash
tail -f /var/log/nginx/error.log
```

### Automated Tasks

The scheduler runs these tasks automatically:

| Task | Schedule | Description |
|------|----------|-------------|
| EPG Import | Hourly | Import EPG data from sources |
| Stream Health Check | Every minute | Verify stream availability |
| Log Cleanup | Daily | Remove old connection logs |
| Failed Jobs Retry | Every 5 minutes | Retry failed queue jobs |

### Database Backup

**Manual Backup:**
```bash
php artisan backup:run
```

**Automated Backup (cron):**
```bash
0 2 * * * cd /var/www/homelabtv && php artisan backup:run >> /dev/null 2>&1
```

Backups are stored in `storage/app/backups/`.

### Maintenance Mode

Enter maintenance mode:
```bash
php artisan down --render="errors::503" --secret="bypass-token"
```

Exit maintenance mode:
```bash
php artisan up
```

Access site during maintenance with: `https://your-domain.com?bypass-token`

---

## Troubleshooting

### Common Issues

#### 1. Queue Jobs Not Processing

**Symptoms:** Jobs stuck in queue, no processing

**Solution:**
```bash
# Check Horizon status
sudo supervisorctl status homelabtv-horizon

# Restart Horizon
sudo supervisorctl restart homelabtv-horizon

# Check logs
tail -f storage/logs/horizon.log
```

#### 2. Streams Not Playing

**Symptoms:** 404 or connection errors on stream URLs

**Checklist:**
- ✅ Stream URL is valid
- ✅ Server is active and configured
- ✅ User has assigned bouquets
- ✅ Stream is in user's bouquet
- ✅ FFmpeg is installed (for transcoding)

**Debug:**
```bash
php artisan homelabtv:check-streams
```

#### 3. Slow Admin Panel

**Symptoms:** Admin dashboard slow to load

**Solution:**
```bash
# Clear cache
php artisan cache:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check database indexes
php artisan db:show --counts
```

#### 4. FFmpeg Transcoding Issues

**Symptoms:** Transcode jobs fail or hang

**Solution:**
```bash
# Check FFmpeg installation
ffmpeg -version

# Test FFmpeg
ffmpeg -i test.mp4 -t 10 -f null -

# Check FFmpeg permissions
which ffmpeg
ls -la $(which ffmpeg)

# Increase queue timeout in config/horizon.php
```

#### 5. Permission Errors

**Symptoms:** 500 errors, file write failures

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/homelabtv
sudo chmod -R 755 /var/www/homelabtv/storage
sudo chmod -R 755 /var/www/homelabtv/bootstrap/cache
```

### Performance Tuning

#### Database Optimization

```bash
# Optimize tables
php artisan db:optimize

# Add indexes (already in migrations)
php artisan migrate
```

#### Redis Tuning

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 1gb
maxmemory-policy allkeys-lru
save ""
appendonly no
```

#### PHP-FPM Pool Settings

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

---

## Security Best Practices

### 1. Change Default Credentials

```bash
php artisan tinker
>>> $admin = User::where('email', 'admin@homelabtv.local')->first();
>>> $admin->password = bcrypt('new_secure_password');
>>> $admin->save();
```

### 2. Enable Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 3. Secure Redis

Edit `/etc/redis/redis.conf`:

```conf
bind 127.0.0.1
requirepass your_redis_password
```

Update `.env`:
```env
REDIS_PASSWORD=your_redis_password
```

### 4. Database Security

```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Secure root account
ALTER USER 'root'@'localhost' IDENTIFIED BY 'strong_password';

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
```

### 5. Regular Updates

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update Composer dependencies
composer update --no-dev

# Update npm packages
npm update
```

---

## Getting Help

### Resources

- **Documentation:** `/docs` directory
- **API Reference:** `/docs/XTREAM_API.md`
- **User Management:** `/docs/USER_MANAGEMENT_BILLING.md`
- **Load Balancer:** `/docs/LOAD_BALANCER_DEPLOYMENT.md`
- **Troubleshooting:** `/docs/TROUBLESHOOTING.md`

### Support

- **Issues:** https://github.com/GitGoneWild/IPTV-Clone/issues
- **Discussions:** https://github.com/GitGoneWild/IPTV-Clone/discussions

---

## License

MIT License - See LICENSE file for details

---

**Made with ❤️ for homelabbers and self-hosters**
