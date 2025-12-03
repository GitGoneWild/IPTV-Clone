# Load Balancer Deployment Guide

## Overview

This guide explains how to deploy and configure load balancers for the IPTV management system. Load balancers distribute content delivery across multiple servers to improve performance, scalability, and reliability.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Docker Deployment](#docker-deployment)
3. [Bare Metal Deployment](#bare-metal-deployment)
4. [Configuration](#configuration)
5. [Monitoring and Health Checks](#monitoring-and-health-checks)
6. [Load Balancing Algorithms](#load-balancing-algorithms)
7. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### Components

```
┌─────────────┐
│   Clients   │ (Flutter Apps, IPTV Players)
└──────┬──────┘
       │
       ↓
┌─────────────┐
│ Main Server │ (API, Admin Panel, Database)
└──────┬──────┘
       │
       ↓ (Distributes traffic)
┌──────────────────────────────────┐
│     Load Balancers (LB1-LBN)     │
│  - Serve content                 │
│  - Report health metrics         │
│  - Auto-register with main       │
└──────────────────────────────────┘
```

### How It Works

1. **Registration**: Load balancers register with the main server on startup
2. **Heartbeat**: LBs send regular heartbeat messages with health stats
3. **Selection**: Clients query the main server for the optimal LB
4. **Streaming**: Content is served directly from the selected LB
5. **Monitoring**: Main server tracks LB health and adjusts routing

---

## Docker Deployment

### Prerequisites

- Docker 20.10+
- Docker Compose 2.0+
- Access to main server API endpoint

### Quick Start

1. **Create Load Balancer Directory**
```bash
mkdir -p ~/iptv-loadbalancer
cd ~/iptv-loadbalancer
```

2. **Copy Dockerfile**
Copy the `Dockerfile.loadbalancer` from the repository to this directory.

3. **Create Configuration File**
Create a `.env` file:
```env
# Main Server Configuration
MAIN_SERVER_URL=https://your-main-server.com
MAIN_SERVER_API_KEY=your-generated-api-key

# Load Balancer Configuration
LB_NAME=LoadBalancer-US-East-1
LB_HOSTNAME=lb1.yourdomain.com
LB_IP_ADDRESS=192.168.1.100
LB_PORT=80
LB_REGION=US-East
LB_WEIGHT=1
LB_MAX_CONNECTIONS=1000

# Optional: Capabilities
LB_CAPABILITIES=hls,rtmp,http

# Health Check Interval (seconds)
HEARTBEAT_INTERVAL=30
```

4. **Build and Run**
```bash
# Build the image
docker build -f Dockerfile.loadbalancer -t iptv-loadbalancer .

# Run the container
docker run -d \
  --name iptv-lb \
  --restart unless-stopped \
  -p 80:80 \
  --env-file .env \
  iptv-loadbalancer
```

### Docker Compose Deployment

For multiple load balancers, use docker-compose:

**docker-compose.loadbalancer.yml:**
```yaml
version: '3.8'

services:
  loadbalancer-1:
    build:
      context: .
      dockerfile: Dockerfile.loadbalancer
    container_name: iptv-lb-1
    restart: unless-stopped
    ports:
      - "8081:80"
    environment:
      - MAIN_SERVER_URL=https://your-main-server.com
      - LB_NAME=LoadBalancer-1
      - LB_HOSTNAME=lb1.yourdomain.com
      - LB_IP_ADDRESS=192.168.1.101
      - LB_PORT=8081
      - LB_REGION=US-East
      - LB_WEIGHT=2
      - LB_MAX_CONNECTIONS=1000
    volumes:
      - ./streams:/var/www/streams:ro
      - ./logs:/var/log/nginx

  loadbalancer-2:
    build:
      context: .
      dockerfile: Dockerfile.loadbalancer
    container_name: iptv-lb-2
    restart: unless-stopped
    ports:
      - "8082:80"
    environment:
      - MAIN_SERVER_URL=https://your-main-server.com
      - LB_NAME=LoadBalancer-2
      - LB_HOSTNAME=lb2.yourdomain.com
      - LB_IP_ADDRESS=192.168.1.102
      - LB_PORT=8082
      - LB_REGION=US-West
      - LB_WEIGHT=1
      - LB_MAX_CONNECTIONS=500
    volumes:
      - ./streams:/var/www/streams:ro
      - ./logs:/var/log/nginx
```

**Deploy:**
```bash
docker-compose -f docker-compose.loadbalancer.yml up -d
```

---

## Bare Metal Deployment

### Prerequisites

- Ubuntu 22.04 LTS or similar
- Nginx or Apache web server
- Python 3.8+ or Node.js 14+ (for heartbeat script)
- curl or wget

### Installation Steps

1. **Install Web Server**
```bash
sudo apt update
sudo apt install -y nginx python3 python3-pip
```

2. **Configure Nginx for Streaming**

Create `/etc/nginx/sites-available/iptv-lb`:
```nginx
server {
    listen 80;
    server_name lb1.yourdomain.com;

    root /var/www/streams;

    # Enable CORS for Flutter apps
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods "GET, OPTIONS";
    add_header Access-Control-Allow-Headers "Origin, Content-Type, Accept, Authorization";

    # Streaming optimization
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    # HLS streaming
    location ~ \.m3u8$ {
        add_header Cache-Control "no-cache";
        add_header Access-Control-Allow-Origin *;
    }

    location ~ \.ts$ {
        add_header Cache-Control "max-age=3600";
        add_header Access-Control-Allow-Origin *;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Status endpoint for monitoring
    location /nginx_status {
        stub_status on;
        access_log off;
        allow 127.0.0.1;
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/iptv-lb /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

3. **Install Heartbeat Script**

Create `/opt/iptv-lb/heartbeat.py`:
```python
#!/usr/bin/env python3
import os
import time
import requests
import psutil
import socket

MAIN_SERVER_URL = os.getenv('MAIN_SERVER_URL', 'https://your-server.com')
API_KEY = os.getenv('MAIN_SERVER_API_KEY')
LB_NAME = os.getenv('LB_NAME', socket.gethostname())
HEARTBEAT_INTERVAL = int(os.getenv('HEARTBEAT_INTERVAL', 30))

def get_system_stats():
    """Get current system statistics"""
    return {
        'cpu_usage': psutil.cpu_percent(interval=1),
        'memory_usage': psutil.virtual_memory().percent,
        'current_connections': len(psutil.net_connections()),
    }

def send_heartbeat():
    """Send heartbeat to main server"""
    try:
        stats = get_system_stats()
        
        response = requests.post(
            f'{MAIN_SERVER_URL}/api/lb/v1/heartbeat',
            headers={'X-LB-API-Key': API_KEY},
            json=stats,
            timeout=10
        )
        
        if response.status_code == 200:
            print(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] Heartbeat sent successfully")
        else:
            print(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] Error: {response.status_code}")
    
    except Exception as e:
        print(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] Failed to send heartbeat: {e}")

def main():
    print(f"Starting heartbeat service for {LB_NAME}")
    print(f"Reporting to: {MAIN_SERVER_URL}")
    print(f"Interval: {HEARTBEAT_INTERVAL} seconds")
    
    while True:
        send_heartbeat()
        time.sleep(HEARTBEAT_INTERVAL)

if __name__ == '__main__':
    main()
```

Make it executable:
```bash
chmod +x /opt/iptv-lb/heartbeat.py
pip3 install requests psutil
```

4. **Create Systemd Service**

Create `/etc/systemd/system/iptv-lb-heartbeat.service`:
```ini
[Unit]
Description=IPTV Load Balancer Heartbeat Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/iptv-lb
Environment="MAIN_SERVER_URL=https://your-server.com"
Environment="MAIN_SERVER_API_KEY=your-api-key"
Environment="LB_NAME=LoadBalancer-1"
ExecStart=/usr/bin/python3 /opt/iptv-lb/heartbeat.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable iptv-lb-heartbeat
sudo systemctl start iptv-lb-heartbeat
sudo systemctl status iptv-lb-heartbeat
```

---

## Configuration

### Environment Variables

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `MAIN_SERVER_URL` | Main server API endpoint | Yes | - |
| `MAIN_SERVER_API_KEY` | API key for authentication | Yes | - |
| `LB_NAME` | Load balancer name | Yes | hostname |
| `LB_HOSTNAME` | Public hostname | Yes | - |
| `LB_IP_ADDRESS` | Public IP address | Yes | - |
| `LB_PORT` | HTTP port | No | 80 |
| `LB_REGION` | Geographic region | No | - |
| `LB_WEIGHT` | Load balancing weight (1-100) | No | 1 |
| `LB_MAX_CONNECTIONS` | Maximum concurrent connections | No | null |
| `HEARTBEAT_INTERVAL` | Seconds between heartbeats | No | 30 |

### Registration

Load balancers must register with the main server before use:

```bash
curl -X POST https://your-server.com/api/lb/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "LoadBalancer-1",
    "hostname": "lb1.yourdomain.com",
    "ip_address": "192.168.1.100",
    "port": 80,
    "region": "US-East",
    "max_connections": 1000,
    "weight": 2
  }'
```

Response includes the API key - **save it securely**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "api_key": "your-unique-api-key-save-this",
    "name": "LoadBalancer-1"
  }
}
```

---

## Monitoring and Health Checks

### Heartbeat Mechanism

Load balancers send heartbeats every 30 seconds (configurable) with:
- Current connection count
- CPU usage
- Memory usage
- Bandwidth statistics
- Response time

### Health Check Endpoint

Each load balancer exposes a health endpoint:
```bash
curl http://lb1.yourdomain.com/health
# Response: healthy
```

### Admin Monitoring

Access load balancer statistics via admin API:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-server.com/api/lb/v1/admin/load-balancers
```

### Metrics Dashboard

Access the Filament admin panel to view:
- Load balancer status
- Connection counts
- CPU/Memory usage
- Geographic distribution
- Historical uptime

---

## Load Balancing Algorithms

### Weight-Based Selection

Load balancers with higher weight receive more traffic:
- Weight 1: Minimum traffic
- Weight 5: 5x more traffic than weight 1
- Weight 10: Maximum traffic

### Capacity-Based Selection

Automatic routing based on available capacity:
```
score = (current_connections / max_connections) * 100
```

Lower scores = more available capacity = higher priority

### Region-Based Routing

Clients can specify preferred region:
- Reduces latency
- Improves user experience
- Automatic fallback to other regions

---

## Troubleshooting

### Load Balancer Not Appearing in Admin Panel

1. Check registration was successful
2. Verify API key is correct
3. Check heartbeat service is running:
   ```bash
   sudo systemctl status iptv-lb-heartbeat
   ```
4. Check network connectivity to main server
5. Review logs: `/var/log/syslog` or Docker logs

### High Load/Connections

1. Check max_connections setting
2. Increase weight to receive less traffic
3. Add more load balancers
4. Scale server resources (CPU, RAM, bandwidth)

### Heartbeat Failures

1. Verify main server URL is accessible
2. Check API key is valid
3. Review firewall rules
4. Check SSL certificate (if using HTTPS)

### Clients Not Getting Load Balancer

1. Ensure at least one LB is online and healthy
2. Check LB status in admin panel
3. Verify LB has capacity (current < max connections)
4. Test optimal LB endpoint:
   ```bash
   curl https://your-server.com/api/flutter/v1/load-balancer/optimal
   ```

---

## Security Best Practices

1. **API Key Protection**
   - Store API keys securely (environment variables, secrets manager)
   - Never commit API keys to version control
   - Rotate keys periodically

2. **Network Security**
   - Use HTTPS for all main server communication
   - Implement firewall rules
   - Restrict access to health/status endpoints

3. **Access Control**
   - Limit registration endpoint access
   - Use IP whitelisting for admin endpoints
   - Monitor for unauthorized registration attempts

4. **DDoS Protection**
   - Implement rate limiting on LB endpoints
   - Use CDN services (Cloudflare, etc.)
   - Monitor for abnormal traffic patterns

---

## Scaling Strategy

### Single Region (1-3 Load Balancers)
- Deploy LBs in same datacenter
- Use weight-based routing
- Ideal for small-medium deployments

### Multi-Region (3-10 Load Balancers)
- Deploy LBs across geographic regions
- Use region-based routing with fallback
- Ideal for global audience

### Large Scale (10+ Load Balancers)
- Multiple LBs per region
- Implement auto-scaling
- Consider CDN integration
- Advanced monitoring and alerting

---

## Maintenance

### Adding a New Load Balancer

1. Deploy using Docker or bare metal
2. Register with main server
3. Start heartbeat service
4. Verify in admin panel
5. Monitor for 24 hours before increasing weight

### Removing a Load Balancer

1. Set `is_active = false` in admin panel
2. Wait for connections to drain
3. Stop heartbeat service
4. Stop web server
5. Remove from infrastructure

### Updating Load Balancer

1. Deploy new LB with updated configuration
2. Reduce weight on old LB
3. Monitor new LB for issues
4. Increase weight on new LB gradually
5. Decommission old LB

---

## Support

For issues or questions:
- GitHub Issues: [Repository URL]
- Documentation: `/docs` directory
- Admin Panel: Check system health widget

---

**Last Updated:** December 3, 2025
**Version:** 1.0.0
