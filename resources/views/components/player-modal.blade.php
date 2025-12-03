{{-- Player Modal Component --}}
{{-- A modern, responsive video player modal for IPTV streams --}}
<div x-data="playerModal()" x-cloak>
    {{-- Modal Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50"
        @click="close()"
    ></div>

    {{-- Modal Content --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="close()"
    >
        <div class="bg-gh-bg-secondary rounded-xl shadow-2xl border border-gh-border w-full max-w-5xl overflow-hidden" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 bg-gh-bg-tertiary border-b border-gh-border">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-1">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gh-success opacity-75" x-show="isPlaying"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isPlaying ? 'bg-gh-success' : 'bg-gh-warning'"></span>
                        </span>
                    </div>
                    <h3 class="text-white font-semibold text-lg truncate max-w-md" x-text="streamTitle"></h3>
                </div>
                <button
                    @click="close()"
                    class="text-gh-text-muted hover:text-white p-2 rounded-lg hover:bg-gh-bg transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Video Container --}}
            <div class="relative bg-black aspect-video">
                {{-- Loading Spinner --}}
                <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/50">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-homelab-500 border-t-transparent"></div>
                </div>

                {{-- Error Message --}}
                <div x-show="hasError" class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 text-center p-4">
                    <svg class="h-16 w-16 text-gh-danger mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-white text-lg font-medium">Failed to load stream</p>
                    <p class="text-gh-text-muted text-sm mt-1" x-text="errorMessage"></p>
                    <button
                        @click="retry()"
                        class="mt-4 px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg transition-colors"
                    >
                        Retry
                    </button>
                </div>

                {{-- Video Element --}}
                <video
                    x-ref="videoPlayer"
                    class="w-full h-full"
                    controls
                    autoplay
                    playsinline
                    @playing="isPlaying = true; isLoading = false"
                    @pause="isPlaying = false"
                    @waiting="isLoading = true"
                    @canplay="isLoading = false"
                    @error="handleError($event)"
                ></video>
            </div>

            {{-- Footer with stream info --}}
            <div class="px-4 py-3 bg-gh-bg-tertiary border-t border-gh-border">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center space-x-4 text-gh-text-muted">
                        <span class="flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span x-text="streamType"></span>
                        </span>
                        <span class="flex items-center" x-show="streamCategory">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span x-text="streamCategory"></span>
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="toggleFullscreen()"
                            class="p-2 text-gh-text-muted hover:text-white hover:bg-gh-bg rounded-lg transition-colors"
                            title="Toggle Fullscreen"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                        </button>
                        <button
                            @click="copyUrl()"
                            class="p-2 text-gh-text-muted hover:text-white hover:bg-gh-bg rounded-lg transition-colors"
                            title="Copy Stream URL"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function playerModal() {
        return {
            isOpen: false,
            isPlaying: false,
            isLoading: false,
            hasError: false,
            errorMessage: '',
            streamUrl: '',
            streamTitle: '',
            streamType: 'HLS',
            streamCategory: '',
            hls: null,

            init() {
                // Listen for custom event to open player
                window.addEventListener('open-player', (e) => this.open(e.detail));
            },

            open(data) {
                this.streamUrl = data.url;
                this.streamTitle = data.title || 'Stream';
                this.streamType = data.type || 'HLS';
                this.streamCategory = data.category || '';
                this.isOpen = true;
                this.isLoading = true;
                this.hasError = false;
                this.errorMessage = '';

                this.$nextTick(() => this.initPlayer());
            },

            close() {
                this.isOpen = false;
                this.isPlaying = false;
                this.destroyPlayer();
            },

            initPlayer() {
                const video = this.$refs.videoPlayer;

                // Check if HLS.js is needed and available
                if (this.streamUrl.includes('.m3u8')) {
                    if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                        this.hls = new Hls({
                            enableWorker: true,
                            lowLatencyMode: true,
                        });
                        this.hls.loadSource(this.streamUrl);
                        this.hls.attachMedia(video);
                        this.hls.on(Hls.Events.ERROR, (event, data) => {
                            if (data.fatal) {
                                this.hasError = true;
                                this.errorMessage = data.details || 'HLS playback error';
                                this.isLoading = false;
                            }
                        });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        // Native HLS support (Safari)
                        video.src = this.streamUrl;
                    } else {
                        this.hasError = true;
                        this.errorMessage = 'HLS playback not supported in this browser';
                        this.isLoading = false;
                        return;
                    }
                } else {
                    // Direct playback for other formats
                    video.src = this.streamUrl;
                }

                video.play().catch(e => {
                    // Autoplay may be blocked, that's ok
                    console.log('Autoplay blocked:', e.message);
                    this.isLoading = false;
                });
            },

            destroyPlayer() {
                if (this.hls) {
                    this.hls.destroy();
                    this.hls = null;
                }
                const video = this.$refs.videoPlayer;
                if (video) {
                    video.pause();
                    video.src = '';
                    video.load();
                }
            },

            handleError(event) {
                this.hasError = true;
                this.isLoading = false;
                const video = this.$refs.videoPlayer;
                const error = video?.error;
                switch (error?.code) {
                    case MediaError.MEDIA_ERR_ABORTED:
                        this.errorMessage = 'Playback aborted';
                        break;
                    case MediaError.MEDIA_ERR_NETWORK:
                        this.errorMessage = 'Network error occurred';
                        break;
                    case MediaError.MEDIA_ERR_DECODE:
                        this.errorMessage = 'Video decode error';
                        break;
                    case MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
                        this.errorMessage = 'Format not supported';
                        break;
                    default:
                        this.errorMessage = 'Unknown playback error';
                }
            },

            retry() {
                this.hasError = false;
                this.isLoading = true;
                this.destroyPlayer();
                this.$nextTick(() => this.initPlayer());
            },

            toggleFullscreen() {
                const video = this.$refs.videoPlayer;
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    video.requestFullscreen();
                }
            },

            async copyUrl() {
                try {
                    await navigator.clipboard.writeText(this.streamUrl);
                    // Could add a toast notification here
                } catch (e) {
                    console.error('Failed to copy URL:', e);
                }
            }
        }
    }
</script>
