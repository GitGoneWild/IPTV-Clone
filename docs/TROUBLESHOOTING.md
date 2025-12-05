# Troubleshooting Guide

This document provides solutions for common issues you may encounter while running HomelabTV.

## Table of Contents

- [Livewire Class Not Found Error](#livewire-class-not-found-error)
- [TMDB API SSL Certificate Error](#tmdb-api-ssl-certificate-error)
- [Stream Playback Issues](#stream-playback-issues)
- [Admin Panel Access Problems](#admin-panel-access-problems)

---

## Livewire Class Not Found Error

### Error Message
```
Class "Livewire\Mechanisms\ExtendBlade\ExtendBlade" not found
```

### Cause
This error occurs when Laravel is trying to load cached Blade template files that contain Livewire directives from a previous version of the application. Livewire is not currently used in this project, so these cached files are outdated.

### Solution

Clear the view cache and other Laravel caches:

```bash
# Clear view cache
php artisan view:clear

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Clear compiled files
php artisan clear-compiled
```

### For Docker Installations

If you're running in Docker, execute the commands inside the container:

```bash
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### Prevention

To prevent this issue in the future:

1. **Always clear caches after updating**: Run cache clearing commands after pulling new code or switching branches
2. **Use a clear script**: Create a script to automate cache clearing (see below)
3. **Check .gitignore**: Ensure `storage/framework/views/` is in `.gitignore` (it is by default)

### Quick Clear Script

You can create a helper script to clear all caches at once. Add this to your project root:

**clear-cache.sh**:
```bash
#!/bin/bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan clear-compiled
echo "✅ All caches cleared successfully!"
```

Make it executable:
```bash
chmod +x clear-cache.sh
./clear-cache.sh
```

### Verification

After clearing caches, verify the issue is resolved by:
1. Accessing the homepage or the page that was showing the error
2. Checking the Laravel logs: `tail -f storage/logs/laravel.log`
3. Ensuring no Livewire-related errors appear

---

## TMDB API SSL Certificate Error

### Error Message
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

### Cause
This error occurs when PHP/cURL cannot verify the SSL certificate of api.themoviedb.org because the local CA (Certificate Authority) bundle is missing, outdated, or not properly configured.

### Solutions

#### Solution 1: Update CA Certificates (Linux)

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ca-certificates
sudo update-ca-certificates

# CentOS/RHEL
sudo yum update ca-certificates

# Alpine Linux
apk add ca-certificates && update-ca-certificates
```

#### Solution 2: Download and Configure CA Bundle Manually

1. Download the latest CA bundle from curl.se:
```bash
curl -o /etc/ssl/certs/cacert.pem https://curl.se/ca/cacert.pem
```

2. Update your `php.ini`:
```ini
; Find your php.ini with: php --ini
curl.cainfo = "/etc/ssl/certs/cacert.pem"
openssl.cafile = "/etc/ssl/certs/cacert.pem"
```

3. Restart PHP/web server:
```bash
sudo systemctl restart php-fpm
sudo systemctl restart nginx  # or apache2
```

#### Solution 3: Docker Container Fix

If running in Docker, add this to your Dockerfile:

```dockerfile
# For Alpine-based images
RUN apk add --no-cache ca-certificates && update-ca-certificates

# For Debian/Ubuntu-based images
RUN apt-get update && apt-get install -y ca-certificates && update-ca-certificates
```

Or add a volume mount for the CA certificates:
```yaml
# docker-compose.yml
services:
  app:
    volumes:
      - /etc/ssl/certs:/etc/ssl/certs:ro
```

#### Solution 4: PHP Configuration (Development Only)

> ⚠️ **Warning**: This disables SSL verification and should only be used for development/testing. Never use in production!

You can temporarily disable SSL verification in your `php.ini` for local development:

```ini
; Add to php.ini (Development ONLY - never use in production!)
curl.cainfo = ""
openssl.cafile = ""
```

Or set the CURL_CA_BUNDLE environment variable to an empty value:

```bash
export CURL_CA_BUNDLE=""
```

**Note**: These workarounds bypass SSL security. Always fix the root cause (missing CA certificates) for production environments.

### Verification

After applying fixes, verify SSL is working:

```bash
# Test SSL connection
curl -v https://api.themoviedb.org/3/configuration?api_key=YOUR_API_KEY

# Test PHP SSL
php -r "echo file_get_contents('https://api.themoviedb.org/3/configuration');"
```

---

## Stream Playback Issues

### Streams Won't Play

1. **Check stream URL**: Ensure the stream URL is valid and accessible
2. **Verify stream status**: Check the stream health status in the admin panel
3. **Browser compatibility**: Try a different browser or check browser console for errors
4. **CORS issues**: Ensure your server allows cross-origin requests for stream URLs

### HLS Streams Not Loading

1. Verify HLS.js is loaded (check browser console)
2. Check if the .m3u8 file is accessible
3. Ensure the stream server supports CORS headers

---

## Admin Panel Access Problems

### Cannot Login to Admin Panel

1. Ensure you have admin privileges (`is_admin = 1` in users table)
2. Clear browser cache and cookies
3. Run `php artisan cache:clear`
4. Check file permissions on `storage/` and `bootstrap/cache/`

### Admin Panel Styling Broken

1. Clear Filament cache: `php artisan filament:clear-cached-components`
2. Rebuild assets: `npm run build`
3. Clear view cache: `php artisan view:clear`

---

## Getting Help

If you continue to experience issues:

1. Check the Laravel logs in `storage/logs/laravel.log`
2. Enable debug mode temporarily in `.env`: `APP_DEBUG=true`
3. Review browser developer tools console for JavaScript errors
4. Open an issue on the GitHub repository with:
   - Error message and stack trace
   - Steps to reproduce
   - Your environment (OS, PHP version, etc.)

---

## Official Documentation Links

- [TMDB API Documentation](https://developers.themoviedb.org/3)
- [Laravel HTTP Client](https://laravel.com/docs/http-client)
- [cURL SSL Certificate Issues](https://curl.se/docs/sslcerts.html)
- [Filament Admin Panel](https://filamentphp.com/docs)
