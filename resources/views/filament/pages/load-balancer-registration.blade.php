<div class="space-y-6">
    <div class="rounded-lg bg-blue-50 dark:bg-blue-950 p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    API Registration Method (Recommended)
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>Use the API endpoint to register load balancers programmatically. This method generates a secure API key automatically.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <h4 class="text-lg font-semibold mb-2">Step 1: Register Load Balancer</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Send a POST request to the registration endpoint:</p>
            
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <code class="text-green-400 text-sm">
curl -X POST {{ config('app.url') }}/api/lb/v1/register \<br>
  -H "Content-Type: application/json" \<br>
  -d '{<br>
    &nbsp;&nbsp;"name": "LoadBalancer-1",<br>
    &nbsp;&nbsp;"hostname": "lb1.yourdomain.com",<br>
    &nbsp;&nbsp;"ip_address": "192.168.1.100",<br>
    &nbsp;&nbsp;"port": 80,<br>
    &nbsp;&nbsp;"region": "US-East",<br>
    &nbsp;&nbsp;"max_connections": 1000,<br>
    &nbsp;&nbsp;"weight": 2<br>
  }'
                </code>
            </div>
        </div>

        <div>
            <h4 class="text-lg font-semibold mb-2">Step 2: Save API Key</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">The response will include an API key. <strong>Save this securely</strong> - it won't be shown again:</p>
            
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <code class="text-yellow-400 text-sm">
{<br>
  &nbsp;&nbsp;"success": true,<br>
  &nbsp;&nbsp;"data": {<br>
    &nbsp;&nbsp;&nbsp;&nbsp;"id": 1,<br>
    &nbsp;&nbsp;&nbsp;&nbsp;"api_key": "your-secure-api-key-here",<br>
    &nbsp;&nbsp;&nbsp;&nbsp;"name": "LoadBalancer-1"<br>
  &nbsp;&nbsp;}<br>
}
                </code>
            </div>
        </div>

        <div>
            <h4 class="text-lg font-semibold mb-2">Step 3: Deploy Load Balancer</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Use Docker or bare metal deployment. For Docker:</p>
            
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <code class="text-cyan-400 text-sm">
docker run -d \<br>
  --name iptv-lb-1 \<br>
  --restart unless-stopped \<br>
  -p 80:80 \<br>
  -e MAIN_SERVER_URL="{{ config('app.url') }}" \<br>
  -e MAIN_SERVER_API_KEY="your-api-key" \<br>
  -e LB_NAME="LoadBalancer-1" \<br>
  -e LB_HOSTNAME="lb1.yourdomain.com" \<br>
  -e LB_IP_ADDRESS="192.168.1.100" \<br>
  -e LB_PORT=80 \<br>
  -v /path/to/streams:/var/www/streams:ro \<br>
  iptv-loadbalancer
                </code>
            </div>
        </div>

        <div>
            <h4 class="text-lg font-semibold mb-2">Step 4: Verify Registration</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">The load balancer will appear in the list above once it sends its first heartbeat. You should see:</p>
            
            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                <li>Status changes from "Offline" to "Online"</li>
                <li>Health indicator turns green</li>
                <li>Last heartbeat timestamp updates every 30 seconds</li>
                <li>Connection count starts being reported</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-lg font-semibold mb-2">Documentation</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">For detailed deployment instructions, see:</p>
        
        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <li><strong>Load Balancer Deployment Guide:</strong> <code>/docs/LOAD_BALANCER_DEPLOYMENT.md</code></li>
            <li><strong>Admin Operations Runbook:</strong> <code>/docs/ADMIN_OPERATIONS.md</code></li>
            <li><strong>API Documentation:</strong> <code>/docs/FLUTTER_API.md</code></li>
        </ul>
    </div>

    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-950 p-4 border border-yellow-200 dark:border-yellow-800">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    Important Security Notes
                </h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>API keys are shown only once during registration - store them securely</li>
                        <li>Use environment variables or secrets management for API keys</li>
                        <li>Never commit API keys to version control</li>
                        <li>Rotate API keys periodically or after suspected compromise</li>
                        <li>Use HTTPS for all main server communication in production</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
