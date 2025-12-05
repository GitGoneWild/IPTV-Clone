# Xtream Codes API Documentation

## Overview

This application provides full compatibility with the Xtream Codes API, allowing any IPTV player that supports Xtream Codes to connect and stream content. The API endpoints follow the industry-standard Xtream Codes specification.

## Authentication

All API endpoints require authentication using one of two methods:

### Method 1: API Token (Recommended)
Use the API token generated for your account:
- **Username**: Your account username
- **Password**: Your API token (found in dashboard)

### Method 2: Legacy Password
For backward compatibility, you can use your account password, but API tokens are more secure and recommended.

## Base URL Formats

The API supports multiple URL formats for compatibility:

```
http://your-domain.com/
http://your-domain.com/api/
```

Both formats work identically. The `/api/` prefix is optional.

## Endpoints

### 1. Player API (`player_api.php`)

**Primary endpoint for retrieving account information, categories, and streams.**

#### Get User Information (Default)

```
GET /player_api.php?username={username}&password={password}
```

**Response:**
```json
{
  "user_info": {
    "username": "testuser",
    "password": "api_token_here",
    "auth": 1,
    "status": "Active",
    "exp_date": 1735689600,
    "is_trial": "0",
    "active_cons": "0",
    "created_at": 1704067200,
    "max_connections": "2",
    "allowed_output_formats": ["m3u8", "ts"]
  },
  "server_info": {
    "url": "http://your-domain.com",
    "port": "80",
    "https_port": "443",
    "server_protocol": "http",
    "rtmp_port": "1935",
    "timezone": "UTC",
    "timestamp_now": 1704067200,
    "time_now": "2024-01-01 00:00:00"
  }
}
```

#### Get Live Categories

```
GET /player_api.php?username={username}&password={password}&action=get_live_categories
```

**Response:**
```json
[
  {
    "category_id": "1",
    "category_name": "Sports",
    "parent_id": "0"
  },
  {
    "category_id": "2",
    "category_name": "News",
    "parent_id": "0"
  }
]
```

#### Get Live Streams

```
GET /player_api.php?username={username}&password={password}&action=get_live_streams
GET /player_api.php?username={username}&password={password}&action=get_live_streams&category_id=1
```

**Response:**
```json
[
  {
    "num": 1,
    "name": "Example Channel",
    "stream_type": "live",
    "stream_id": 1,
    "stream_icon": "http://example.com/logo.png",
    "epg_channel_id": "channel-1",
    "added": 1704067200,
    "is_adult": "0",
    "category_id": "1",
    "custom_sid": "",
    "tv_archive": 0,
    "direct_source": "",
    "tv_archive_duration": 0
  }
]
```

#### Get Short EPG

```
GET /player_api.php?username={username}&password={password}&action=get_short_epg&stream_id=1&limit=4
```

**Response:**
```json
{
  "epg_listings": [
    {
      "id": 1,
      "epg_id": 1,
      "title": "UHJvZ3JhbSBUaXRsZQ==",
      "lang": "en",
      "start": "2024-01-01 12:00:00",
      "end": "2024-01-01 13:00:00",
      "description": "UHJvZ3JhbSBEZXNjcmlwdGlvbg==",
      "channel_id": "channel-1",
      "start_timestamp": 1704110400,
      "stop_timestamp": 1704114000,
      "stream_id": 1
    }
  ]
}
```

Note: Title and description are base64-encoded in the response.

#### Get Simple Data Table

```
GET /player_api.php?username={username}&password={password}&action=get_simple_data_table
```

**Response:**
```json
{
  "live_categories": [...],
  "live_streams": [...]
}
```

Returns combined categories and streams data in a single request.

### 2. M3U Playlist (`get.php`)

**Generate M3U playlist file containing all available streams.**

```
GET /get.php?username={username}&password={password}&type=m3u_plus
GET /get.php?username={username}&password={password}&type=m3u_plus&output=ts
```

**Alternative URL Format:**
```
GET /{username}/{password}
```

**Parameters:**
- `type`: `m3u` or `m3u_plus` (default: `m3u_plus`)
- `output`: `ts`, `m3u8` (default: `ts`)

**Response:**
```
#EXTM3U url-tvg="http://your-domain.com/xmltv.php?username=user&password=token"
#EXTINF:-1 tvg-id="channel-1" tvg-name="Example Channel" tvg-logo="http://example.com/logo.png" group-title="Sports",Example Channel
http://your-domain.com/live/user/token/1.ts
```

### 3. XMLTV EPG (`xmltv.php`)

**Generate XMLTV-formatted Electronic Program Guide.**

```
GET /xmltv.php?username={username}&password={password}
```

**Response:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tv SYSTEM "xmltv.dtd">
<tv generator-info-name="HomelabTV" generator-info-url="http://your-domain.com">
  <channel id="channel-1">
    <display-name>Example Channel</display-name>
    <icon src="http://example.com/logo.png" />
  </channel>
  <programme start="20240101120000 +0000" stop="20240101130000 +0000" channel="channel-1">
    <title lang="en">Program Title</title>
    <desc lang="en">Program Description</desc>
    <category lang="en">Entertainment</category>
  </programme>
</tv>
```

### 4. Panel API (`panel_api.php`)

**Get comprehensive panel data including user info, server info, categories, and available channels.**

```
GET /panel_api.php?username={username}&password={password}
POST /panel_api.php
```

**Response:**
```json
{
  "user_info": {...},
  "server_info": {...},
  "categories": [...],
  "available_channels": [1, 2, 3, 4]
}
```

### 5. Enigma2 Bouquet (`enigma2.php`)

**Generate Enigma2-compatible bouquet file for satellite receivers.**

```
GET /enigma2.php?username={username}&password={password}
```

**Response:**
```
#NAME HomelabTV
#SERVICE 4097:0:1:0:0:0:0:0:0:0:http%3A%2F%2Fyour-domain.com%2Flive%2Fuser%2Ftoken%2F1.ts:Example Channel
#DESCRIPTION Example Channel
```

### 6. Direct Stream URLs

**Access streams directly by ID with multiple format support.**

```
GET /live/{username}/{password}/{stream_id}
GET /live/{username}/{password}/{stream_id}.ts
GET /live/{username}/{password}/{stream_id}.m3u8
```

**Example:**
```
http://your-domain.com/live/testuser/token123/1.ts
```

These URLs redirect (HTTP 302) to the actual stream URL.

## Error Responses

### Authentication Failed
**HTTP 401**
```json
{
  "user_info": {
    "auth": 0,
    "status": "Disabled",
    "message": "Invalid credentials"
  }
}
```

### Account Expired or Inactive
**HTTP 401**
```json
{
  "user_info": {
    "auth": 0,
    "status": "Disabled",
    "message": "Invalid credentials"
  }
}
```

### Stream Not Found
**HTTP 404**
```
Stream not found
```

### Access Denied
**HTTP 403**
```
Account expired or inactive
```

## Usage Examples

### VLC Media Player

1. Open VLC
2. Media → Open Network Stream
3. Enter M3U URL: `http://your-domain.com/get.php?username=user&password=token&type=m3u_plus`
4. Click Play

### IPTV Smarters Pro

1. Open IPTV Smarters Pro
2. Select "Login with Xtream Codes API"
3. Enter:
   - **Server URL**: `http://your-domain.com`
   - **Username**: Your username
   - **Password**: Your API token
4. Click "Add User"

### TiviMate

1. Open TiviMate
2. Add Playlist
3. Select "Xtream Codes"
4. Enter:
   - **Server**: `your-domain.com`
   - **Username**: Your username
   - **Password**: Your API token
5. Click "Next"

### Kodi with PVR IPTV Simple Client

1. Install PVR IPTV Simple Client addon
2. Configure addon:
   - **Location**: Remote Path (Internet Address)
   - **M3U Play List URL**: `http://your-domain.com/get.php?username=user&password=token&type=m3u_plus`
   - **XMLTV URL**: `http://your-domain.com/xmltv.php?username=user&password=token`
3. Enable addon
4. Restart Kodi

## Security Considerations

### API Tokens vs Passwords

- **Use API tokens**: More secure as they don't expose your account password
- **Regenerate tokens**: If a token is compromised, regenerate it in your dashboard
- **Never share URLs publicly**: Your credentials are embedded in the URLs

### HTTPS

- **Use HTTPS**: Encrypt credentials in transit
- **Avoid HTTP**: Credentials sent over HTTP are visible to network sniffers

### Rate Limiting

- API endpoints are rate-limited to prevent abuse
- Default: 100 requests per minute per IP address
- Contact administrator if legitimate use requires higher limits

## Testing

A comprehensive test suite is available in `tests/Feature/XtreamApiTest.php` covering:

- User authentication (valid/invalid credentials)
- Account status checks (active/expired/inactive)
- All API endpoints (player_api, get.php, xmltv, panel_api, enigma2)
- Various URL formats and parameter combinations
- EPG data retrieval
- Direct stream access
- Error handling

Run tests with:
```bash
php artisan test --filter XtreamApiTest
```

## Troubleshooting

### "Invalid credentials" Error

1. Verify username is correct
2. Use API token from dashboard, not account password
3. Check account is active and not expired
4. Ensure no typos in URL

### No Streams Showing

1. Verify you have bouquets/packages assigned
2. Check account expiration date
3. Ensure streams are active and not hidden
4. Contact administrator if issue persists

### EPG Not Loading

1. Verify XMLTV URL is correct
2. Check streams have EPG channel IDs assigned
3. Ensure EPG data has been imported by administrator
4. Clear IPTV player cache and reload

### Stream Won't Play

1. Check stream is active in admin panel
2. Verify stream URL is accessible
3. Test stream with VLC directly
4. Check server capacity and bandwidth
5. Verify your connection count hasn't exceeded limit

## API Compatibility

This implementation is compatible with:

- ✅ Xtream Codes API v2
- ✅ IPTV Smarters Pro
- ✅ TiviMate
- ✅ Perfect Player
- ✅ GSE Smart IPTV
- ✅ XCIPTV
- ✅ VLC Media Player
- ✅ Kodi (PVR IPTV Simple Client)
- ✅ Plex (via M3U)
- ✅ Emby (via M3U)

## Support

For issues or questions:
1. Check this documentation
2. Review test suite for usage examples
3. Contact system administrator
4. Check application logs for detailed error messages

## Changelog

### 2024-12-05
- Fixed return type hints in XtreamController for proper type safety
- Added support for route parameters in authentication (/{username}/{password} format)
- Fixed default action in playerApi to return user info when no action specified
- Added comprehensive test suite with 19 tests covering all endpoints
- Enhanced documentation for all endpoints
- All tests passing successfully
