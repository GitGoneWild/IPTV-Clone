# HomelabTV - Complete API Reference

Complete API documentation for HomelabTV's three API layers: Xtream Codes API, Modern Flutter API, and REST API.

## Table of Contents

- [Authentication](#authentication)
- [Xtream Codes Compatible API](#xtream-codes-compatible-api)
- [Modern Flutter API](#modern-flutter-api)
- [REST API](#rest-api)
- [Rate Limiting](#rate-limiting)
- [Error Handling](#error-handling)

---

## Authentication

HomelabTV supports multiple authentication methods depending on the API layer being used.

### API Token Authentication (Recommended)

Generate API tokens from the admin panel for each user. These tokens can be used in place of passwords.

**Advantages:**
- ✅ Secure - tokens can be revoked without changing passwords
- ✅ Auditable - track token usage
- ✅ Granular - different tokens for different clients

**Generate Token:**
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->generateApiToken();
>>> $user->api_token;
```

### Password Authentication

Legacy compatibility for Xtream Codes API clients.

**⚠️ Warning:** Passwords in URLs can be exposed in logs. Use API tokens in production.

### Sanctum Token Authentication

For modern API clients (Flutter, mobile apps).

**Login to get token:**
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

**Use token:**
```http
GET /api/flutter/v1/live/streams
Authorization: Bearer 1|abc123...
```

---

## Xtream Codes Compatible API

Full compatibility with Xtream Codes panel API. Works with all standard IPTV players.

### Base URL

```
http://your-domain.com/
```

### Authentication Parameters

All endpoints require:
- `username`: User's username
- `password`: User's API token (recommended) or password

### Endpoints

#### 1. Player API (`/player_api.php`)

Main API endpoint for all player operations.

**User Info (Default Action)**

```http
GET /player_api.php?username=demo&password=api_token
```

Response:
```json
{
  "user_info": {
    "username": "demo",
    "password": "api_token",
    "status": "Active",
    "exp_date": "1735689600",
    "is_trial": "0",
    "active_cons": "0",
    "created_at": "1672531200",
    "max_connections": "3",
    "allowed_output_formats": ["m3u8", "ts", "rtmp"]
  },
  "server_info": {
    "url": "http://your-domain.com",
    "port": "8080",
    "https_port": "443",
    "server_protocol": "http",
    "rtmp_port": "1935",
    "timezone": "UTC",
    "timestamp_now": 1735689600,
    "time_now": "2025-01-01 00:00:00"
  }
}
```

**Get Live Categories**

```http
GET /player_api.php?username=demo&password=api_token&action=get_live_categories
```

Response:
```json
[
  {
    "category_id": "1",
    "category_name": "Sports",
    "parent_id": 0
  },
  {
    "category_id": "2",
    "category_name": "News",
    "parent_id": 0
  }
]
```

**Get Live Streams**

```http
GET /player_api.php?username=demo&password=api_token&action=get_live_streams
```

Optional parameter: `category_id` to filter by category

Response:
```json
[
  {
    "num": 1,
    "name": "ESPN HD",
    "stream_type": "live",
    "stream_id": 1,
    "stream_icon": "http://your-domain.com/logos/espn.png",
    "epg_channel_id": "espn.us",
    "added": "1672531200",
    "category_id": "1",
    "custom_sid": "",
    "tv_archive": 0,
    "direct_source": "",
    "tv_archive_duration": 0
  }
]
```

**Get VOD Categories**

```http
GET /player_api.php?username=demo&password=api_token&action=get_vod_categories
```

**Get VOD Streams**

```http
GET /player_api.php?username=demo&password=api_token&action=get_vod_streams
```

Optional: `category_id` parameter

**Get VOD Info**

```http
GET /player_api.php?username=demo&password=api_token&action=get_vod_info&vod_id=123
```

Response:
```json
{
  "info": {
    "tmdb_id": "550",
    "name": "Fight Club",
    "cover": "https://image.tmdb.org/t/p/w500/poster.jpg",
    "plot": "An insomniac office worker...",
    "cast": "Brad Pitt, Edward Norton",
    "director": "David Fincher",
    "genre": "Drama",
    "release_date": "1999-10-15",
    "rating": "8.8",
    "duration": "139 min",
    "bitrate": 5000
  },
  "movie_data": {
    "stream_id": 123,
    "name": "Fight Club",
    "added": "1672531200",
    "category_id": "5",
    "container_extension": "mp4",
    "custom_sid": "",
    "direct_source": ""
  }
}
```

**Get Series Categories**

```http
GET /player_api.php?username=demo&password=api_token&action=get_series_categories
```

**Get Series**

```http
GET /player_api.php?username=demo&password=api_token&action=get_series
```

Optional: `category_id` parameter

**Get Series Info**

```http
GET /player_api.php?username=demo&password=api_token&action=get_series_info&series_id=456
```

Response includes all seasons and episodes:
```json
{
  "seasons": [
    {
      "season_number": 1,
      "name": "Season 1",
      "episode_count": 10,
      "cover": "https://...",
      "plot": "..."
    }
  ],
  "episodes": {
    "1": [
      {
        "id": "789",
        "episode_num": 1,
        "title": "Pilot",
        "container_extension": "mp4",
        "info": {
          "plot": "...",
          "duration": "45 min",
          "rating": "8.5"
        }
      }
    ]
  }
}
```

**Get Short EPG**

```http
GET /player_api.php?username=demo&password=api_token&action=get_short_epg&stream_id=1&limit=4
```

Response:
```json
{
  "epg_listings": [
    {
      "id": "1",
      "epg_id": "12345",
      "title": "Sports News",
      "lang": "en",
      "start": "2025-01-01 18:00:00",
      "end": "2025-01-01 19:00:00",
      "description": "Latest sports updates",
      "channel_id": "espn.us"
    }
  ]
}
```

#### 2. M3U Playlist (`/get.php`)

Generate M3U playlist with all available streams.

```http
GET /get.php?username=demo&password=api_token&type=m3u_plus&output=ts
```

**Parameters:**
- `type`: Playlist format (m3u_plus, m3u)
- `output`: Stream format (ts, m3u8)

**Alternative URL Format:**
```http
GET /demo/api_token
```

Response (text/plain):
```m3u
#EXTM3U x-tvg-url="http://your-domain.com/xmltv.php?username=demo&password=api_token"
#EXTINF:-1 tvg-id="espn.us" tvg-name="ESPN HD" tvg-logo="http://your-domain.com/logos/espn.png" group-title="Sports",ESPN HD
http://your-domain.com/live/demo/api_token/1.ts
```

#### 3. XMLTV EPG (`/xmltv.php`)

Electronic Program Guide in XMLTV format.

```http
GET /xmltv.php?username=demo&password=api_token
```

Response (application/xml):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tv SYSTEM "xmltv.dtd">
<tv generator-info-name="HomelabTV">
  <channel id="espn.us">
    <display-name>ESPN HD</display-name>
    <icon src="http://your-domain.com/logos/espn.png"/>
  </channel>
  <programme start="20250101180000 +0000" stop="20250101190000 +0000" channel="espn.us">
    <title lang="en">Sports News</title>
    <desc lang="en">Latest sports updates</desc>
  </programme>
</tv>
```

#### 4. Panel API (`/panel_api.php`)

Comprehensive panel data including all available content.

```http
GET /panel_api.php?username=demo&password=api_token
```

Response:
```json
{
  "user_info": { /* ... */ },
  "server_info": { /* ... */ },
  "categories": {
    "live": [ /* ... */ ],
    "vod": [ /* ... */ ],
    "series": [ /* ... */ ]
  },
  "available_channels": [ /* ... */ ]
}
```

#### 5. Enigma2 Bouquet (`/enigma2.php`)

Bouquet file for Enigma2 devices (Dreambox, VU+).

```http
GET /enigma2.php?username=demo&password=api_token
```

#### 6. Direct Stream URLs

Access streams directly without player API calls.

**Formats:**
```http
GET /live/{username}/{password}/{stream_id}
GET /live/{username}/{password}/{stream_id}.ts
GET /live/{username}/{password}/{stream_id}.m3u8
```

**Example:**
```http
GET /live/demo/api_token/1.ts
```

---

## Modern Flutter API

RESTful API designed for Flutter/mobile applications with Sanctum authentication.

### Base URL

```
http://your-domain.com/api/flutter/v1
```

### Authentication

All authenticated endpoints require Bearer token:

```http
Authorization: Bearer {sanctum_token}
```

### Public Endpoints

#### Get EPG Data

```http
GET /api/flutter/v1/epg?date=2025-01-01
```

Parameters:
- `date` (optional): Filter by date (YYYY-MM-DD)
- `channel_id` (optional): Filter by channel
- `per_page` (optional): Results per page (default: 50)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "channel_id": "espn.us",
      "title": "Sports News",
      "description": "Latest updates",
      "start_time": "2025-01-01T18:00:00Z",
      "end_time": "2025-01-01T19:00:00Z",
      "language": "en"
    }
  ],
  "links": { /* pagination */ },
  "meta": { /* pagination meta */ }
}
```

#### Get Current/Next EPG

```http
GET /api/flutter/v1/epg/current-next/{channel_id}
```

Response:
```json
{
  "current": {
    "title": "Live Event",
    "start_time": "2025-01-01T18:00:00Z",
    "end_time": "2025-01-01T20:00:00Z"
  },
  "next": {
    "title": "Next Show",
    "start_time": "2025-01-01T20:00:00Z",
    "end_time": "2025-01-01T21:00:00Z"
  }
}
```

#### Get Categories

```http
GET /api/flutter/v1/categories
```

Optional: `type` parameter (live, movies, series)

#### Search Content

```http
GET /api/flutter/v1/search?q=sports&type=live
```

Parameters:
- `q`: Search query
- `type` (optional): Content type (live, movies, series)
- `per_page` (optional): Results per page

#### Get Optimal Load Balancer

```http
GET /api/flutter/v1/load-balancer/optimal?region=us-east
```

Response:
```json
{
  "id": 1,
  "name": "US East 1",
  "url": "https://lb1.your-domain.com",
  "region": "us-east",
  "load": 45,
  "health_status": "healthy"
}
```

### Authenticated Endpoints

#### Get Live Streams

```http
GET /api/flutter/v1/live/streams
Authorization: Bearer {token}
```

Parameters:
- `category_id` (optional): Filter by category
- `search` (optional): Search term
- `per_page` (optional): Results per page (default: 20)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "ESPN HD",
      "stream_url": "http://...",
      "stream_type": "live",
      "logo_url": "http://...",
      "epg_channel_id": "espn.us",
      "is_active": true,
      "category": {
        "id": 1,
        "name": "Sports"
      }
    }
  ],
  "links": { /* pagination */ },
  "meta": { /* pagination meta */ }
}
```

#### Get Single Stream

```http
GET /api/flutter/v1/live/streams/{stream_id}
Authorization: Bearer {token}
```

#### Get Movies (VOD)

```http
GET /api/flutter/v1/movies
Authorization: Bearer {token}
```

Parameters:
- `category_id` (optional): Filter by category
- `search` (optional): Search term
- `year` (optional): Release year
- `genre` (optional): Genre filter
- `per_page` (optional): Results per page

#### Get Single Movie

```http
GET /api/flutter/v1/movies/{movie_id}
Authorization: Bearer {token}
```

Response:
```json
{
  "id": 123,
  "title": "Fight Club",
  "plot": "An insomniac office worker...",
  "poster": "https://...",
  "backdrop": "https://...",
  "trailer_url": "https://...",
  "release_date": "1999-10-15",
  "runtime": 139,
  "rating": 8.8,
  "genre": "Drama",
  "director": "David Fincher",
  "cast": "Brad Pitt, Edward Norton",
  "stream_url": "http://..."
}
```

#### Get TV Series

```http
GET /api/flutter/v1/series
Authorization: Bearer {token}
```

#### Get Series Detail

```http
GET /api/flutter/v1/series/{series_id}
Authorization: Bearer {token}
```

Response includes seasons and episodes.

#### Get Season Episodes

```http
GET /api/flutter/v1/series/{series_id}/seasons/{season_number}
Authorization: Bearer {token}
```

#### Get Episode Detail

```http
GET /api/flutter/v1/episodes/{episode_id}
Authorization: Bearer {token}
```

---

## REST API

Simple REST API for custom integrations.

### Base URL

```
http://your-domain.com/api/v1
```

### Authentication

Requires Sanctum token:

```http
Authorization: Bearer {token}
```

### Endpoints

#### Get Current User

```http
GET /api/v1/user
Authorization: Bearer {token}
```

Response:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "username": "john",
  "status": "active",
  "exp_date": "2025-12-31",
  "max_connections": 3
}
```

#### Get User's Streams

```http
GET /api/v1/streams
Authorization: Bearer {token}
```

#### Get User's Bouquets

```http
GET /api/v1/bouquets
Authorization: Bearer {token}
```

#### Get EPG for Channel

```http
GET /api/v1/epg/{channel_id}
Authorization: Bearer {token}
```

---

## Rate Limiting

### Limits

| API Layer | Limit | Window |
|-----------|-------|--------|
| Xtream Codes API | 100 requests | per minute |
| Flutter API | 100 requests | per minute |
| REST API | 60 requests | per minute |
| Web Routes | 60 requests | per minute |

### Headers

Rate limit information in response headers:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1735689600
```

### Rate Limit Exceeded

**Response (429 Too Many Requests):**
```json
{
  "message": "Too Many Requests",
  "retry_after": 60
}
```

---

## Error Handling

### Standard Error Response

```json
{
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

### Common Errors

#### Authentication Failed

```json
{
  "error": "Invalid credentials",
  "code": "AUTH_FAILED"
}
```

#### Resource Not Found

```json
{
  "error": "Stream not found",
  "code": "NOT_FOUND"
}
```

#### Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Best Practices

### 1. Use API Tokens

Always prefer API tokens over passwords for IPTV clients.

### 2. Enable HTTPS

Use HTTPS in production to protect credentials and data.

### 3. Handle Rate Limits

Implement exponential backoff when rate limited.

### 4. Cache Responses

Cache EPG and playlist data to reduce API calls.

### 5. Use Appropriate Endpoints

- **IPTV Players:** Use Xtream Codes API
- **Mobile Apps:** Use Flutter API
- **Custom Integrations:** Use REST API

### 6. Monitor Usage

Track API usage via Horizon dashboard.

---

## Examples

### cURL Examples

**Get User Info:**
```bash
curl -X GET "http://your-domain.com/player_api.php?username=demo&password=api_token"
```

**Get M3U Playlist:**
```bash
curl -X GET "http://your-domain.com/get.php?username=demo&password=api_token&type=m3u_plus"
```

**Search Content (Flutter API):**
```bash
curl -X GET "http://your-domain.com/api/flutter/v1/search?q=sports" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Python Example

```python
import requests

# Authenticate
url = "http://your-domain.com/player_api.php"
params = {
    "username": "demo",
    "password": "api_token"
}

response = requests.get(url, params=params)
data = response.json()

print(f"User: {data['user_info']['username']}")
print(f"Status: {data['user_info']['status']}")
```

### JavaScript Example

```javascript
// Using Fetch API
const username = 'demo';
const password = 'api_token';
const url = `http://your-domain.com/player_api.php?username=${username}&password=${password}`;

fetch(url)
  .then(response => response.json())
  .then(data => {
    console.log('User:', data.user_info.username);
    console.log('Status:', data.user_info.status);
  });
```

---

## Support

For API support:
- Check `/docs/XTREAM_API.md` for detailed Xtream API documentation
- Check `/docs/FLUTTER_API.md` for Flutter API details
- Open an issue: https://github.com/GitGoneWild/IPTV-Clone/issues

---

**API Version:** v1.0  
**Last Updated:** December 2025
