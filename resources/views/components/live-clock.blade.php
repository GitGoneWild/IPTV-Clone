<!-- Live Clock Component -->
<div 
    x-data="{ 
        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }),
        interval: null,
        updateTime() {
            this.time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
    }" 
    x-init="interval = setInterval(() => updateTime(), 1000)"
    x-destroy="clearInterval(interval)"
    class="hidden sm:flex items-center text-gh-text-muted text-sm px-3 py-2"
>
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <span x-text="time" class="font-mono"></span>
</div>
