#!/usr/bin/env python3
"""
Load Balancer Heartbeat Service

Sends regular heartbeat messages to the main server with system statistics.
"""

import os
import sys
import time
import json
import signal
import logging
from datetime import datetime
import requests

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger('iptv-lb-heartbeat')

# Configuration from environment variables
MAIN_SERVER_URL = os.getenv('MAIN_SERVER_URL')
API_KEY = os.getenv('MAIN_SERVER_API_KEY')
HEARTBEAT_INTERVAL = int(os.getenv('HEARTBEAT_INTERVAL', '30'))
LB_NAME = os.getenv('LB_NAME', 'LoadBalancer')

# Global flag for graceful shutdown
running = True


def signal_handler(signum, frame):
    """Handle shutdown signals gracefully"""
    global running
    logger.info(f'Received signal {signum}, shutting down...')
    running = False


def get_system_stats():
    """
    Get current system statistics
    Returns dict with CPU, memory, and connection stats
    """
    try:
        import psutil
        
        # Get network connections count
        connections = len(psutil.net_connections())
        
        # Get system stats
        cpu_percent = psutil.cpu_percent(interval=1)
        memory = psutil.virtual_memory()
        net_io = psutil.net_io_counters()
        
        return {
            'current_connections': connections,
            'cpu_usage': round(cpu_percent, 2),
            'memory_usage': round(memory.percent, 2),
            'bandwidth_in': net_io.bytes_recv,
            'bandwidth_out': net_io.bytes_sent,
            'status': 'online'
        }
    except ImportError:
        # Fallback if psutil is not available
        logger.warning('psutil not available, using basic stats')
        return {
            'current_connections': 0,
            'status': 'online'
        }
    except Exception as e:
        logger.error(f'Error getting system stats: {e}')
        return {
            'current_connections': 0,
            'status': 'online'
        }


def get_nginx_stats():
    """
    Get Nginx connection statistics from stub_status
    Returns dict with active connections
    """
    try:
        response = requests.get('http://localhost/nginx_status', timeout=2)
        if response.status_code == 200:
            # Parse nginx stub_status output
            # Format: Active connections: 1
            lines = response.text.strip().split('\n')
            for line in lines:
                if 'Active connections:' in line:
                    connections = int(line.split(':')[1].strip())
                    return {'current_connections': connections}
        return {}
    except Exception as e:
        logger.debug(f'Could not get nginx stats: {e}')
        return {}


def send_heartbeat():
    """
    Send heartbeat to main server with current statistics
    """
    if not MAIN_SERVER_URL or not API_KEY:
        logger.error('MAIN_SERVER_URL or API_KEY not configured')
        return False
    
    try:
        # Combine system and nginx stats
        stats = get_system_stats()
        nginx_stats = get_nginx_stats()
        
        # Nginx stats take precedence for connection count
        if 'current_connections' in nginx_stats:
            stats['current_connections'] = nginx_stats['current_connections']
        
        # Calculate response time by pinging main server
        start_time = time.time()
        requests.get(MAIN_SERVER_URL, timeout=5)
        response_time = int((time.time() - start_time) * 1000)
        stats['response_time_ms'] = response_time
        
        # Send heartbeat
        endpoint = f'{MAIN_SERVER_URL}/api/lb/v1/heartbeat'
        headers = {
            'X-LB-API-Key': API_KEY,
            'Content-Type': 'application/json'
        }
        
        response = requests.post(
            endpoint,
            headers=headers,
            json=stats,
            timeout=10
        )
        
        if response.status_code == 200:
            logger.info(f'Heartbeat sent successfully - Connections: {stats.get("current_connections", 0)}, '
                       f'CPU: {stats.get("cpu_usage", 0)}%, Memory: {stats.get("memory_usage", 0)}%')
            return True
        else:
            logger.error(f'Heartbeat failed with status {response.status_code}: {response.text}')
            return False
    
    except requests.exceptions.RequestException as e:
        logger.error(f'Failed to send heartbeat: {e}')
        return False
    except Exception as e:
        logger.error(f'Unexpected error sending heartbeat: {e}')
        return False


def main():
    """
    Main loop for heartbeat service
    """
    # Register signal handlers
    signal.signal(signal.SIGTERM, signal_handler)
    signal.signal(signal.SIGINT, signal_handler)
    
    logger.info(f'Starting heartbeat service for {LB_NAME}')
    logger.info(f'Reporting to: {MAIN_SERVER_URL}')
    logger.info(f'Interval: {HEARTBEAT_INTERVAL} seconds')
    
    # Initial heartbeat
    send_heartbeat()
    
    # Main loop
    while running:
        try:
            time.sleep(HEARTBEAT_INTERVAL)
            if running:  # Check again after sleep
                send_heartbeat()
        except KeyboardInterrupt:
            logger.info('Received keyboard interrupt')
            break
        except Exception as e:
            logger.error(f'Error in main loop: {e}')
            time.sleep(5)  # Wait before retrying
    
    logger.info('Heartbeat service stopped')


if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        logger.error(f'Fatal error: {e}')
        sys.exit(1)
