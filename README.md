# HomelabTV - Private IPTV Management Panel

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-FDAE4B?style=flat-square)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)](LICENSE)

A self-hosted IPTV management panel designed for homelab enthusiasts to manage legal streams, CCTV cameras, and private channels.

> ⚠️ **Legal Notice**: This software is intended for private homelab use with legally sourced streams only. Do not use for piracy or illegal content distribution.

## Features

### Admin Panel (FilamentPHP)
- 🎬 **Stream Management**: Support for HLS, MPEG-TS, RTMP, and HTTP streams
- 📁 **Categories & Subcategories**: Organize your streams hierarchically
- 📺 **EPG Import**: XMLTV file upload and URL import with automatic updates
- 🖥️ **Server Management**: Multiple streaming servers with load balancing
- 📦 **Bouquets**: Channel packages for user assignment

### User Management
- 👤 Create users with username/password
- ⏰ Expiry dates and max connection limits
- 📋 Allowed output formats (M3U, Xtream, Enigma2)
- 🏷️ Assign bouquets per user
- 💰 Optional reseller system with credits

### Xtream Codes Compatible API
Works with any IPTV player supporting Xtream Codes:
- `/player_api.php` - Main API endpoint
- `/get.php` - M3U playlist generation
- `/panel_api.php` - Panel data
- `/xmltv.php` - EPG data (XMLTV format)
- `/enigma2.php` - Enigma2 bouquet file
- `/live/{username}/{password}/{stream_id}` - Direct stream URLs

### Additional Features
- 🔒 REST API with Laravel Sanctum tokens
- 📊 Stream status monitoring (online/offline)
- 🚦 Rate limiting and security hardening
- 🎨 Dark GitHub-style theme with purple accents

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Admin Panel**: Filament 3
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS 3
- **Authentication**: Laravel Sanctum
- **Database**: MySQL / MariaDB
- **Cache**: Redis
- **Containerization**: Docker + docker-compose

## Quick Start with Docker

### Prerequisites
- Docker & Docker Compose
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/homelabtv.git
   cd homelabtv
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies and setup**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   docker-compose exec app php artisan storage:link
   ```

5. **Access the application**
   - Frontend: http://localhost:8080
   - Admin Panel: http://localhost:8080/admin

### Default Credentials

| User Type | Email | Username | Password |
|-----------|-------|----------|----------|
| Admin | admin@homelabtv.local | admin | admin123 |
| Demo User | demo@homelabtv.local | demo | demo123 |
| Reseller | reseller@homelabtv.local | reseller | reseller123 |

## Manual Installation (Without Docker)

### Requirements
- PHP 8.2+
- Composer
- MySQL 8.0+ or MariaDB 10.6+
- Redis
- Node.js & NPM (for asset compilation)

### Steps

1. **Clone and install dependencies**
   ```bash
   git clone https://github.com/yourusername/homelabtv.git
   cd homelabtv
   composer install
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=homelabtv
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Start development server**
   ```bash
   php artisan serve
   ```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `HOMELABTV_DEFAULT_PORT` | Default streaming port | 8080 |
| `HOMELABTV_STREAM_CHECK_INTERVAL` | Stream health check interval (seconds) | 60 |
| `HOMELABTV_EPG_UPDATE_INTERVAL` | EPG auto-update interval (seconds) | 3600 |
| `HOMELABTV_MAX_CONNECTIONS_PER_USER` | Default max connections | 1 |
| `HOMELABTV_ENABLE_RESELLER_SYSTEM` | Enable reseller features | true |
| `RATE_LIMIT_PER_MINUTE` | Web rate limit | 60 |
| `API_RATE_LIMIT_PER_MINUTE` | API rate limit | 100 |

## API Usage

### Getting User Info
```bash
curl "http://localhost:8080/player_api.php?username=demo&password=demo123"
```

### Getting Live Streams
```bash
curl "http://localhost:8080/player_api.php?username=demo&password=demo123&action=get_live_streams"
```

### Getting M3U Playlist
```bash
curl "http://localhost:8080/get.php?username=demo&password=demo123&type=m3u_plus"
```

### Getting EPG (XMLTV)
```bash
curl "http://localhost:8080/xmltv.php?username=demo&password=demo123"
```

## Scheduled Tasks

The application includes automated tasks:

| Task | Schedule | Description |
|------|----------|-------------|
| `homelabtv:import-epg` | Hourly | Import EPG data from sources |
| `homelabtv:check-streams` | Every minute | Check stream health status |
| `homelabtv:cleanup-logs` | Daily | Clean old connection logs |

To run the scheduler, add this cron entry:
```bash
* * * * * cd /path-to-homelabtv && php artisan schedule:run >> /dev/null 2>&1
```

## Project Structure

```
homelabtv/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Filament/             # Admin panel resources
│   │   ├── Resources/        # CRUD resources
│   │   └── Widgets/          # Dashboard widgets
│   ├── Http/
│   │   ├── Controllers/      # Web & API controllers
│   │   └── Middleware/       # Custom middleware
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   └── Services/             # Business logic
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docker/                   # Docker configuration
├── public/                   # Public assets
├── resources/views/          # Blade templates
├── routes/                   # Route definitions
└── storage/                  # File storage
```

## Security

- All API endpoints are rate-limited
- Passwords are hashed using bcrypt
- CSRF protection on all forms
- XSS protection headers
- SQL injection prevention via Eloquent ORM

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Filament](https://filamentphp.com) - Admin Panel
- [Tailwind CSS](https://tailwindcss.com) - Styling
- [Livewire](https://livewire.laravel.com) - Frontend Components