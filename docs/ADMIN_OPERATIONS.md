# Admin Operations Runbook

## Overview

This runbook provides step-by-step procedures for common administrative tasks, troubleshooting, and maintenance operations for the IPTV Management System.

## Table of Contents

1. [System Health Checks](#system-health-checks)
2. [User Management](#user-management)
3. [Content Management](#content-management)
4. [Load Balancer Operations](#load-balancer-operations)
5. [API Management](#api-management)
6. [Performance Monitoring](#performance-monitoring)
7. [Backup and Recovery](#backup-and-recovery)
8. [Troubleshooting](#troubleshooting)
9. [Emergency Procedures](#emergency-procedures)

---

## System Health Checks

### Daily Health Check

Run this command to verify system health:

```bash
php artisan homelabtv:health-check
```

Expected output should show all checks passing:
- ✓ Database connection
- ✓ Redis connection (if enabled)
- ✓ Storage permissions
- ✓ EPG directory
- ✓ Critical configuration

### Weekly Maintenance Tasks

1. **Check disk space**
   ```bash
   df -h
   ```
   
2. **Review error logs**
   ```bash
   tail -n 100 storage/logs/laravel.log
   ```

3. **Check database size**
   ```bash
   php artisan db:show
   ```

4. **Review API rate limits**
   ```bash
   # Check logs for rate limit violations
   grep "429" storage/logs/laravel.log | tail -n 50
   ```

---

## User Management

### Create a New User

**Via Admin Panel:**
1. Navigate to `/admin/users`
2. Click "Create User"
3. Fill in required fields:
   - Username (unique)
   - Email
   - Password
   - Role (user/reseller/admin)
   - Expiry date
   - Max connections
4. Assign bouquets
5. Save

**Via CLI:**
```bash
php artisan tinker

# Create user
$user = \App\Models\User::create([
    'username' => 'newuser',
    'email' => 'user@example.com',
    'password' => bcrypt('secure-password'),
    'role' => 'user',
    'is_active' => true,
    'expires_at' => now()->addMonths(3),
    'max_connections' => 2,
]);

# Generate API token
$token = $user->createToken('api-token')->plainTextToken;
echo "API Token: $token\n";
```

### Generate API Token for Existing User

```bash
php artisan tinker

$user = \App\Models\User::where('username', 'demo')->first();
$token = $user->createToken('flutter-app')->plainTextToken;
echo "Token: $token\n";
```

### Disable a User

**Via Admin Panel:**
1. Go to `/admin/users`
2. Find the user
3. Edit → Set "Is Active" to false
4. Save

**Via CLI:**
```bash
php artisan tinker

$user = \App\Models\User::where('username', 'problem_user')->first();
$user->update(['is_active' => false]);
```

### Revoke All User Tokens

```bash
php artisan tinker

$user = \App\Models\User::where('username', 'username')->first();
$user->tokens()->delete();
echo "All tokens revoked\n";
```

### Extend User Subscription

```bash
php artisan tinker

$user = \App\Models\User::where('username', 'username')->first();
$user->update([
    'expires_at' => now()->addMonths(3)
]);
echo "Subscription extended\n";
```

---

## Content Management

### Import EPG Data

**From URL:**
```bash
php artisan homelabtv:import-epg --url="https://example.com/epg.xml"
```

**From File:**
```bash
php artisan homelabtv:import-epg --file="/path/to/epg.xml"
```

### Add Bulk Streams via CLI

```bash
php artisan tinker

$streams = [
    ['name' => 'BBC One', 'stream_url' => 'http://...', 'category_id' => 1],
    ['name' => 'BBC Two', 'stream_url' => 'http://...', 'category_id' => 1],
];

foreach ($streams as $data) {
    \App\Models\Stream::create($data);
}
```

### Check Stream Health

```bash
php artisan homelabtv:check-streams
```

This command:
- Tests each active stream
- Updates online/offline status
- Logs results

### Bulk Import Movies from TMDB

```bash
php artisan tinker

// Search for a movie
$service = app(\App\Services\TmdbService::class);
$results = $service->searchMovie('The Matrix');

// Import the movie
$movie = \App\Models\Movie::create([
    'title' => $results[0]['title'],
    'tmdb_id' => $results[0]['id'],
    'release_year' => substr($results[0]['release_date'], 0, 4),
    // ... other fields
]);
```

---

## Load Balancer Operations

### List All Load Balancers

**Via API:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/lb/v1/admin/load-balancers
```

**Via Database:**
```bash
php artisan tinker

$lbs = \App\Models\LoadBalancer::all();
foreach ($lbs as $lb) {
    echo "{$lb->name}: {$lb->status} - {$lb->current_connections}/{$lb->max_connections}\n";
}
```

### Check Load Balancer Health

```bash
php artisan tinker

$lb = \App\Models\LoadBalancer::find(1);
echo "Healthy: " . ($lb->isHealthy() ? 'Yes' : 'No') . "\n";
echo "Load: {$lb->load_percentage}%\n";
echo "Last heartbeat: " . $lb->last_heartbeat_at . "\n";
```

### Manually Register a Load Balancer

```bash
curl -X POST https://your-domain.com/api/lb/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "LoadBalancer-3",
    "hostname": "lb3.yourdomain.com",
    "ip_address": "192.168.1.103",
    "port": 80,
    "region": "EU-West",
    "max_connections": 1000,
    "weight": 2
  }'
```

### Deactivate a Load Balancer

```bash
php artisan tinker

$lb = \App\Models\LoadBalancer::where('name', 'LoadBalancer-1')->first();
$lb->update(['is_active' => false]);
echo "Load balancer deactivated\n";
```

### Get Load Balancer Statistics

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/lb/v1/admin/load-balancers/1/stats
```

### Force Load Balancer Offline

```bash
php artisan tinker

$lb = \App\Models\LoadBalancer::find(1);
$lb->update(['status' => 'maintenance']);
echo "Load balancer set to maintenance mode\n";
```

---

## API Management

### Check API Rate Limits

```bash
# View recent API usage
php artisan tinker

$logs = \App\Models\ApiUsageLog::latest()->limit(100)->get();
foreach ($logs as $log) {
    echo "{$log->created_at} - {$log->endpoint} - {$log->user_id}\n";
}
```

### Adjust Rate Limits

Edit `.env`:
```env
API_RATE_LIMIT_PER_MINUTE=100
RATE_LIMIT_PER_MINUTE=60
```

Then restart:
```bash
php artisan config:cache
php artisan cache:clear
```

### Test API Endpoints

**Test EPG endpoint:**
```bash
curl "https://your-domain.com/api/flutter/v1/epg?limit=5"
```

**Test authenticated endpoint:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/flutter/v1/live/streams?per_page=5"
```

### View API Token Usage

```bash
php artisan tinker

$user = \App\Models\User::where('username', 'demo')->first();
$tokens = $user->tokens;

foreach ($tokens as $token) {
    echo "Token: {$token->name} - Last used: {$token->last_used_at}\n";
}
```

---

## Performance Monitoring

### Check Database Query Performance

```bash
# Enable query log
php artisan tinker

DB::enableQueryLog();

// Run some operations
$streams = \App\Models\Stream::with('category')->paginate(20);

// View queries
dd(DB::getQueryLog());
```

### Monitor Cache Performance

```bash
php artisan tinker

// Check cache status
$stats = Cache::get('some_key');

// Clear specific cache
Cache::forget('epg_current_next_bbc1');

// Clear all cache
Cache::flush();
```

### Check Connection Logs

```bash
php artisan tinker

// Recent connections
$logs = \App\Models\ConnectionLog::latest()->limit(100)->get();
foreach ($logs as $log) {
    echo "{$log->user->username} - {$log->stream->name} - {$log->created_at}\n";
}

// Active connections
$active = \App\Models\ConnectionLog::whereNull('disconnected_at')->count();
echo "Active connections: $active\n";
```

### Database Maintenance

**Optimize tables:**
```bash
php artisan tinker

DB::statement('OPTIMIZE TABLE streams');
DB::statement('OPTIMIZE TABLE epg_programs');
DB::statement('OPTIMIZE TABLE connection_logs');
```

**Clean old logs:**
```bash
php artisan homelabtv:cleanup-logs
```

---

## Backup and Recovery

### Database Backup

**Manual backup:**
```bash
# MySQL/MariaDB
mysqldump -u username -p homelabtv > backup_$(date +%Y%m%d_%H%M%S).sql

# SQLite
cp database/database.sqlite backup_$(date +%Y%m%d_%H%M%S).sqlite
```

**Automated backup script:**
```bash
#!/bin/bash
BACKUP_DIR="/backups/iptv"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup
mysqldump -u username -p homelabtv | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Keep only last 7 days
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

### Restore from Backup

```bash
# MySQL/MariaDB
mysql -u username -p homelabtv < backup_20251203_120000.sql

# Or from gzipped backup
gunzip < backup_20251203_120000.sql.gz | mysql -u username -p homelabtv
```

### Backup EPG Data

```bash
# Backup EPG files
tar -czf epg_backup_$(date +%Y%m%d).tar.gz storage/app/epg/

# Backup uploaded files
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz storage/app/public/
```

### Full System Backup

```bash
#!/bin/bash
BACKUP_DIR="/backups/iptv/full"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p "$BACKUP_DIR/$DATE"

# Backup database
mysqldump -u username -p homelabtv | gzip > "$BACKUP_DIR/$DATE/database.sql.gz"

# Backup .env
cp .env "$BACKUP_DIR/$DATE/.env"

# Backup storage
tar -czf "$BACKUP_DIR/$DATE/storage.tar.gz" storage/

# Backup docker volumes (if using Docker)
# docker run --rm -v iptv_data:/data -v $BACKUP_DIR/$DATE:/backup alpine tar czf /backup/volumes.tar.gz /data

echo "Full backup completed: $DATE"
```

---

## Troubleshooting

### Problem: Users Can't Authenticate

**Check:**
1. User is active: `is_active = true`
2. User hasn't expired: `expires_at > NOW()`
3. API token is valid
4. Rate limits not exceeded

**Fix:**
```bash
php artisan tinker

$user = \App\Models\User::where('username', 'problem_user')->first();

// Check status
echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "Expires: " . $user->expires_at . "\n";

// Reactivate if needed
$user->update([
    'is_active' => true,
    'expires_at' => now()->addMonths(1)
]);
```

### Problem: Streams Not Playing

**Check:**
1. Stream is marked as active
2. Server is accessible
3. Stream URL is valid

**Test stream:**
```bash
# Test with ffprobe
ffprobe -v error "http://stream-url.com/stream.m3u8"

# Test with curl
curl -I "http://stream-url.com/stream.m3u8"
```

**Fix in database:**
```bash
php artisan tinker

$stream = \App\Models\Stream::find(1);
$stream->update(['is_active' => true]);

// Update stream URL if needed
$stream->update(['stream_url' => 'http://new-url.com/stream.m3u8']);
```

### Problem: EPG Not Updating

**Check:**
1. EPG source is accessible
2. Cron job is running
3. EPG file is valid XML

**Manual update:**
```bash
php artisan homelabtv:import-epg --force
```

**Check EPG data:**
```bash
php artisan tinker

$programs = \App\Models\EpgProgram::where('channel_id', 'bbc1')
    ->where('start_time', '>=', now())
    ->limit(5)
    ->get();

foreach ($programs as $p) {
    echo "{$p->title} - {$p->start_time}\n";
}
```

### Problem: Load Balancer Not Responding

**Check:**
1. Load balancer service is running
2. Network connectivity
3. Heartbeat service is active
4. API key is valid

**Verify heartbeat:**
```bash
# On load balancer server
systemctl status iptv-lb-heartbeat

# Check logs
journalctl -u iptv-lb-heartbeat -n 50

# Test connection to main server
curl -I https://your-main-server.com
```

**Reset load balancer:**
```bash
# On main server
php artisan tinker

$lb = \App\Models\LoadBalancer::find(1);
$lb->update([
    'status' => 'online',
    'last_heartbeat_at' => now(),
]);
```

### Problem: High Server Load

**Identify bottleneck:**
```bash
# Check system resources
top
htop

# Check database connections
mysql -e "SHOW PROCESSLIST;"

# Check slow queries
php artisan telescope:list

# Check active connections
php artisan tinker
$count = \App\Models\ConnectionLog::whereNull('disconnected_at')->count();
echo "Active connections: $count\n";
```

**Solutions:**
1. Add more load balancers
2. Enable Redis caching
3. Optimize database queries
4. Scale server resources
5. Implement rate limiting

### Problem: API Rate Limit Exceeded

**Identify source:**
```bash
php artisan tinker

$logs = \App\Models\ApiUsageLog::where('created_at', '>=', now()->subHour())
    ->groupBy('ip_address')
    ->selectRaw('ip_address, COUNT(*) as count')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($logs as $log) {
    echo "{$log->ip_address}: {$log->count} requests\n";
}
```

**Temporary solution:**
```bash
# Increase rate limit temporarily
php artisan config:set api.rate_limit 200
php artisan config:cache
```

---

## Emergency Procedures

### System Down - Quick Recovery

1. **Check system status:**
   ```bash
   systemctl status nginx
   systemctl status php-fpm
   systemctl status mysql
   ```

2. **Restart services:**
   ```bash
   systemctl restart nginx
   systemctl restart php-fpm
   systemctl restart mysql
   ```

3. **Check logs:**
   ```bash
   tail -f /var/log/nginx/error.log
   tail -f storage/logs/laravel.log
   ```

4. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

### Database Corruption

1. **Stop application:**
   ```bash
   php artisan down
   ```

2. **Check and repair tables:**
   ```bash
   mysqlcheck -u username -p --auto-repair homelabtv
   ```

3. **Restore from backup if needed:**
   ```bash
   mysql -u username -p homelabtv < latest_backup.sql
   ```

4. **Bring application back up:**
   ```bash
   php artisan up
   ```

### Security Breach Response

1. **Immediate actions:**
   ```bash
   # Revoke all API tokens
   php artisan tinker
   \Illuminate\Support\Facades\DB::table('personal_access_tokens')->delete();
   
   # Disable affected users
   \App\Models\User::where('id', 'IN', [...])->update(['is_active' => false]);
   ```

2. **Review logs:**
   ```bash
   grep "401\|403\|429" storage/logs/laravel.log | tail -n 100
   ```

3. **Change sensitive credentials:**
   - Database passwords
   - Admin passwords
   - API keys
   - Application key: `php artisan key:generate`

4. **Enable maintenance mode:**
   ```bash
   php artisan down --secret="emergency-access-token"
   # Access via: https://your-domain.com/emergency-access-token
   ```

### Complete System Restore

1. **Restore database:**
   ```bash
   mysql -u username -p homelabtv < backup.sql
   ```

2. **Restore files:**
   ```bash
   tar -xzf storage_backup.tar.gz
   ```

3. **Restore .env:**
   ```bash
   cp backup/.env .env
   ```

4. **Clear and rebuild:**
   ```bash
   composer install
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan storage:link
   ```

5. **Verify:**
   ```bash
   php artisan homelabtv:health-check
   ```

---

## Maintenance Windows

### Scheduled Maintenance Procedure

1. **Announce maintenance:**
   - Update status page
   - Send user notifications
   - Set maintenance window (e.g., 2 AM - 4 AM)

2. **Enable maintenance mode:**
   ```bash
   php artisan down --render="errors::503"
   ```

3. **Perform maintenance:**
   - Database updates
   - Server upgrades
   - Configuration changes

4. **Test thoroughly:**
   ```bash
   php artisan homelabtv:health-check
   # Test critical endpoints
   ```

5. **Disable maintenance mode:**
   ```bash
   php artisan up
   ```

6. **Monitor for issues:**
   - Check error logs
   - Monitor load balancers
   - Verify API responses

---

## Useful Commands Reference

```bash
# Health check
php artisan homelabtv:health-check

# Import EPG
php artisan homelabtv:import-epg --url="..."

# Check streams
php artisan homelabtv:check-streams

# Cleanup old logs
php artisan homelabtv:cleanup-logs

# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# View routes
php artisan route:list

# Database migrations
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh --seed

# Generate API documentation
php artisan route:list --json > api-routes.json
```

---

## Contact and Escalation

For critical issues:
1. Check this runbook first
2. Review application logs
3. Check GitHub issues
4. Contact system administrator
5. Escalate to development team if needed

---

**Last Updated:** December 3, 2025
**Version:** 1.0.0
