# Flutter API Documentation

## Overview

This document provides comprehensive documentation for the Modern Flutter API endpoints designed for production-ready IPTV applications. The API follows RESTful principles and provides organized access to EPG, Live TV, Movies, and Series content.

## Base URL

```
Production: https://your-domain.com/api/flutter/v1
Development: http://localhost:8080/api/flutter/v1
```

## Authentication

Most endpoints require authentication using Laravel Sanctum tokens. Include the token in the `Authorization` header:

```
Authorization: Bearer YOUR_API_TOKEN
```

Some endpoints are public and only require rate limiting:
- EPG endpoints
- Categories
- Search
- Load balancer optimal selection

## Rate Limiting

All API endpoints are rate limited:
- **API Rate Limit**: 100 requests per minute per IP
- Exceeded limits return HTTP 429 (Too Many Requests)

## Response Format

All responses follow a consistent JSON format:

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

## EPG Endpoints

### Get EPG Programs

Retrieve EPG (Electronic Program Guide) data for channels.

**Endpoint:** `GET /epg`

**Authentication:** Public (rate limited)

**Query Parameters:**
- `channel_id` (string, optional) - Filter by channel ID
- `date` (string, optional) - Date in Y-m-d format (e.g., 2025-12-03)
- `limit` (integer, optional) - Number of programs (default: 50, max: 200)

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/epg?channel_id=bbc1&limit=10"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "channel_id": "bbc1",
      "title": "News at 6",
      "description": "Evening news program",
      "start_time": "2025-12-03T18:00:00.000000Z",
      "end_time": "2025-12-03T18:30:00.000000Z",
      "category": "News",
      "lang": "en"
    }
  ],
  "count": 10,
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

### Get Current and Next Program

Get the currently playing and next program for a specific channel.

**Endpoint:** `GET /epg/current-next/{channelId}`

**Authentication:** Public (rate limited)

**URL Parameters:**
- `channelId` (string, required) - The channel ID

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/epg/current-next/bbc1"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "current": {
      "id": 1,
      "title": "News at 6",
      "start_time": "2025-12-03T18:00:00.000000Z",
      "end_time": "2025-12-03T18:30:00.000000Z"
    },
    "next": {
      "id": 2,
      "title": "Drama Show",
      "start_time": "2025-12-03T18:30:00.000000Z",
      "end_time": "2025-12-03T19:30:00.000000Z"
    }
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

## Live TV Endpoints

### Get Live Streams

Retrieve a paginated list of live TV streams.

**Endpoint:** `GET /live/streams`

**Authentication:** Required (Sanctum token)

**Query Parameters:**
- `category_id` (integer, optional) - Filter by category ID
- `search` (string, optional) - Search in stream names
- `page` (integer, optional) - Page number (default: 1)
- `per_page` (integer, optional) - Items per page (default: 20, max: 100)

**Example Request:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/flutter/v1/live/streams?category_id=1&per_page=20"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "BBC One",
      "stream_url": "http://server.com/stream.m3u8",
      "stream_type": "hls",
      "logo_url": "http://cdn.com/logo.png",
      "epg_channel_id": "bbc1",
      "is_active": true,
      "category": {
        "id": 1,
        "name": "UK Channels"
      },
      "server": {
        "id": 1,
        "name": "Primary Server"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

### Get Single Live Stream

Get detailed information about a specific live stream.

**Endpoint:** `GET /live/streams/{streamId}`

**Authentication:** Required (Sanctum token)

**URL Parameters:**
- `streamId` (integer, required) - The stream ID

**Example Request:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/flutter/v1/live/streams/1"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "stream": {
      "id": 1,
      "name": "BBC One",
      "stream_url": "http://server.com/stream.m3u8",
      "category": { ... },
      "server": { ... }
    },
    "epg": {
      "current": { ... },
      "next": { ... }
    }
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

### Get Live TV Categories

Get all categories containing live streams.

**Endpoint:** `GET /categories/live`

**Authentication:** Public (rate limited)

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/categories/live"
```

---

## VOD (Movies) Endpoints

### Get Movies

Retrieve a paginated list of movies with filtering and sorting.

**Endpoint:** `GET /movies`

**Authentication:** Required (Sanctum token)

**Query Parameters:**
- `category_id` (integer, optional) - Filter by category
- `search` (string, optional) - Search in title/original title
- `genre` (string, optional) - Filter by genre
- `year` (integer, optional) - Filter by release year
- `sort` (string, optional) - Sort field: title, release_year, rating, tmdb_rating (default: title)
- `order` (string, optional) - Sort order: asc, desc (default: asc)
- `page` (integer, optional) - Page number
- `per_page` (integer, optional) - Items per page (default: 20, max: 100)

**Example Request:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/flutter/v1/movies?genre=Action&sort=rating&order=desc&per_page=20"
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Action Movie",
      "original_title": "Original Action Movie",
      "plot": "An exciting action movie...",
      "genre": "Action, Thriller",
      "runtime": 120,
      "rating": "PG-13",
      "tmdb_rating": 8.5,
      "release_year": 2024,
      "poster_url": "http://cdn.com/poster.jpg",
      "backdrop_url": "http://cdn.com/backdrop.jpg",
      "stream_url": "http://server.com/movie.mp4",
      "category": {
        "id": 2,
        "name": "Action Movies"
      }
    }
  ],
  "pagination": { ... },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

### Get Single Movie

Get detailed information about a specific movie.

**Endpoint:** `GET /movies/{movieId}`

**Authentication:** Required (Sanctum token)

**URL Parameters:**
- `movieId` (integer, required) - The movie ID

---

## Series Endpoints

### Get TV Series

Retrieve a paginated list of TV series with filtering and sorting.

**Endpoint:** `GET /series`

**Authentication:** Required (Sanctum token)

**Query Parameters:**
- `category_id` (integer, optional) - Filter by category
- `search` (string, optional) - Search in title/original title
- `genre` (string, optional) - Filter by genre
- `status` (string, optional) - Filter by status (ongoing, ended, etc.)
- `sort` (string, optional) - Sort field: title, release_year, rating, tmdb_rating
- `order` (string, optional) - Sort order: asc, desc
- `page` (integer, optional) - Page number
- `per_page` (integer, optional) - Items per page (default: 20, max: 100)

**Example Request:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://your-domain.com/api/flutter/v1/series?genre=Drama&per_page=20"
```

---

### Get Series Details

Get detailed information about a TV series including all seasons and episodes.

**Endpoint:** `GET /series/{seriesId}`

**Authentication:** Required (Sanctum token)

**URL Parameters:**
- `seriesId` (integer, required) - The series ID

**Example Response:**
```json
{
  "success": true,
  "data": {
    "series": {
      "id": 1,
      "title": "Great TV Show",
      "plot": "An amazing series...",
      "num_seasons": 3,
      "num_episodes": 30
    },
    "seasons": [
      {
        "season_number": 1,
        "episode_count": 10,
        "episodes": [ ... ]
      },
      {
        "season_number": 2,
        "episode_count": 10,
        "episodes": [ ... ]
      }
    ]
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

### Get Season Episodes

Get all episodes for a specific season of a series.

**Endpoint:** `GET /series/{seriesId}/seasons/{seasonNumber}`

**Authentication:** Required (Sanctum token)

**URL Parameters:**
- `seriesId` (integer, required) - The series ID
- `seasonNumber` (integer, required) - The season number

---

### Get Episode Details

Get detailed information about a specific episode.

**Endpoint:** `GET /episodes/{episodeId}`

**Authentication:** Required (Sanctum token)

**URL Parameters:**
- `episodeId` (integer, required) - The episode ID

---

## Categories Endpoint

### Get All Categories

Get all content categories with optional filtering.

**Endpoint:** `GET /categories`

**Authentication:** Public (rate limited)

**Query Parameters:**
- `type` (string, optional) - Filter by content type: live, movie, series

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/categories?type=movie"
```

---

## Search Endpoint

### Universal Search

Search across all content types (live streams, movies, series).

**Endpoint:** `GET /search`

**Authentication:** Public (rate limited)

**Query Parameters:**
- `q` (string, required) - Search query (min: 2 chars, max: 255)
- `types` (array, optional) - Content types to search: live, movie, series (default: all)
- `limit` (integer, optional) - Results per content type (default: 10, max: 50)

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/search?q=action&types[]=movie&types[]=series&limit=5"
```

**Example Response:**
```json
{
  "success": true,
  "query": "action",
  "data": {
    "live_streams": [ ... ],
    "movies": [ ... ],
    "series": [ ... ]
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

## Load Balancer Endpoints

### Get Optimal Load Balancer

Get the optimal load balancer for a client based on region and current load.

**Endpoint:** `GET /load-balancer/optimal`

**Authentication:** Public (rate limited)

**Query Parameters:**
- `region` (string, optional) - Preferred region (e.g., US-East, EU-West)
- `client_ip` (string, optional) - Client IP address

**Example Request:**
```bash
curl "https://your-domain.com/api/flutter/v1/load-balancer/optimal?region=US-East"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "LB US-East-1",
    "hostname": "lb1.yourdomain.com",
    "port": 80,
    "base_url": "http://lb1.yourdomain.com",
    "region": "US-East",
    "load_percentage": 45.5
  },
  "timestamp": "2025-12-03T20:00:00.000000Z"
}
```

---

## Error Codes

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Missing or invalid token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |
| 503 | Service Unavailable - No load balancers available |

---

## Best Practices

### 1. Use Pagination
Always use pagination for list endpoints to improve performance:
```
GET /movies?page=1&per_page=20
```

### 2. Cache Responses
Cache API responses on the client side to reduce server load:
- EPG data: 5 minutes
- Categories: 1 hour
- Movie/Series listings: 15 minutes

### 3. Handle Rate Limits
Implement exponential backoff when receiving 429 responses.

### 4. Use Load Balancers
Always fetch the optimal load balancer before streaming:
1. Call `/load-balancer/optimal` to get the best server
2. Use the returned `base_url` for streaming content

### 5. Error Handling
Always check the `success` field in responses and handle errors gracefully.

---

## Flutter Integration Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class IptvApiClient {
  final String baseUrl = 'https://your-domain.com/api/flutter/v1';
  final String token;

  IptvApiClient(this.token);

  Future<List<Movie>> getMovies({
    int page = 1,
    int perPage = 20,
    String? genre,
  }) async {
    final queryParams = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (genre != null) 'genre': genre,
    };

    final uri = Uri.parse('$baseUrl/movies')
        .replace(queryParameters: queryParams);

    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      if (data['success']) {
        return (data['data'] as List)
            .map((json) => Movie.fromJson(json))
            .toList();
      }
    }

    throw Exception('Failed to load movies');
  }

  Future<String> getOptimalLoadBalancer({String? region}) async {
    final queryParams = {
      if (region != null) 'region': region,
    };

    final uri = Uri.parse('$baseUrl/load-balancer/optimal')
        .replace(queryParameters: queryParams);

    final response = await http.get(uri);

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      if (data['success']) {
        return data['data']['base_url'];
      }
    }

    throw Exception('No load balancers available');
  }
}
```

---

## Support

For issues or questions about the API:
- GitHub Issues: [Repository URL]
- Documentation: This file
- Admin Panel: Access via `/admin` route

---

**Last Updated:** December 3, 2025
**API Version:** 1.0.0
