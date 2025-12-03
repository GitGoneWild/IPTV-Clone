#!/bin/bash
# Load Balancer Registration Script
# Registers this load balancer with the main server on startup

set -e

echo "=== IPTV Load Balancer Registration ==="
echo "Main Server: ${MAIN_SERVER_URL}"
echo "Load Balancer: ${LB_NAME}"
echo ""

# Check required environment variables
if [ -z "$MAIN_SERVER_URL" ]; then
    echo "ERROR: MAIN_SERVER_URL not set"
    exit 1
fi

if [ -z "$LB_NAME" ]; then
    echo "ERROR: LB_NAME not set"
    exit 1
fi

if [ -z "$LB_HOSTNAME" ]; then
    echo "ERROR: LB_HOSTNAME not set"
    exit 1
fi

if [ -z "$LB_IP_ADDRESS" ]; then
    echo "ERROR: LB_IP_ADDRESS not set"
    exit 1
fi

# Check if already registered (API key exists)
if [ -n "$MAIN_SERVER_API_KEY" ]; then
    echo "API key found, skipping registration (already registered)"
    exit 0
fi

# Build registration payload
PAYLOAD=$(cat <<EOF
{
    "name": "${LB_NAME}",
    "hostname": "${LB_HOSTNAME}",
    "ip_address": "${LB_IP_ADDRESS}",
    "port": ${LB_PORT:-80},
    "region": "${LB_REGION:-}",
    "max_connections": ${LB_MAX_CONNECTIONS:-null},
    "weight": ${LB_WEIGHT:-1},
    "capabilities": ${LB_CAPABILITIES:-[]}
}
EOF
)

echo "Registering load balancer..."
echo "Payload: $PAYLOAD"
echo ""

# Send registration request
RESPONSE=$(curl -s -X POST \
    -H "Content-Type: application/json" \
    -d "$PAYLOAD" \
    "${MAIN_SERVER_URL}/api/lb/v1/register" \
    -w "\nHTTP_CODE:%{http_code}")

# Extract HTTP status code
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE:/d')

echo "Response Code: $HTTP_CODE"
echo "Response Body: $BODY"
echo ""

# Check if registration was successful
if [ "$HTTP_CODE" = "201" ]; then
    echo "✓ Registration successful!"
    
    # Extract API key from response
    API_KEY=$(echo "$BODY" | grep -o '"api_key":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$API_KEY" ]; then
        echo ""
        echo "=========================================="
        echo "IMPORTANT: Save this API key securely!"
        echo "API Key: $API_KEY"
        echo "=========================================="
        echo ""
        echo "Set this in your environment:"
        echo "export MAIN_SERVER_API_KEY='$API_KEY'"
        echo ""
        
        # Save to file for reference (not secure for production)
        echo "$API_KEY" > /opt/iptv-lb/.api_key
        chmod 600 /opt/iptv-lb/.api_key
        echo "API key saved to: /opt/iptv-lb/.api_key"
    else
        echo "WARNING: Could not extract API key from response"
    fi
else
    echo "✗ Registration failed with HTTP $HTTP_CODE"
    echo "Please check the error message and try again"
    exit 1
fi

echo ""
echo "=== Registration Complete ==="
