<?php

namespace App\Services;

use App\Models\Stream;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for verifying stream health and connectivity.
 *
 * Provides detailed diagnostics for stream verification including
 * error types, HTTP status codes, and troubleshooting information.
 */
class StreamVerificationService
{
    /** @var int HTTP timeout in seconds for stream checks */
    protected int $timeout;

    /** Error type constants */
    public const ERROR_NONE = null;

    public const ERROR_TIMEOUT = 'timeout';

    public const ERROR_CONNECTION = 'connection_failed';

    public const ERROR_DNS = 'dns_resolution_failed';

    public const ERROR_SSL = 'ssl_error';

    public const ERROR_HTTP_4XX = 'http_client_error';

    public const ERROR_HTTP_5XX = 'http_server_error';

    public const ERROR_INVALID_URL = 'invalid_url';

    public const ERROR_INVALID_FORMAT = 'invalid_stream_format';

    public const ERROR_UNKNOWN = 'unknown_error';

    public function __construct()
    {
        $this->timeout = config('homelabtv.stream_check_timeout', 10);
    }

    /**
     * Verify a stream and return detailed results.
     *
     * @return array{
     *     status: string,
     *     is_online: bool,
     *     error_type: string|null,
     *     error_message: string|null,
     *     http_status: int|null,
     *     response_time_ms: int|null,
     *     content_type: string|null,
     *     checked_at: string
     * }
     */
    public function verify(Stream $stream): array
    {
        $startTime = microtime(true);
        $url = $stream->getEffectiveUrl();

        // Validate URL first
        if (! $this->isValidUrl($url)) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_INVALID_URL,
                errorMessage: 'Invalid or malformed stream URL',
            );
        }

        try {
            return $this->checkStreamByType($stream, $url, $startTime);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->handleConnectionException($e, $startTime);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return $this->handleRequestException($e, $startTime);
        } catch (\Exception $e) {
            Log::warning("Stream verification error for stream {$stream->id}: {$e->getMessage()}");

            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_UNKNOWN,
                errorMessage: $e->getMessage(),
                responseTimeMs: $this->calculateResponseTime($startTime),
            );
        }
    }

    /**
     * Verify a stream URL directly without a Stream model.
     *
     * @return array{
     *     status: string,
     *     is_online: bool,
     *     error_type: string|null,
     *     error_message: string|null,
     *     http_status: int|null,
     *     response_time_ms: int|null,
     *     content_type: string|null,
     *     checked_at: string
     * }
     */
    public function verifyUrl(string $url, string $streamType = 'hls'): array
    {
        $startTime = microtime(true);

        if (! $this->isValidUrl($url)) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_INVALID_URL,
                errorMessage: 'Invalid or malformed stream URL',
            );
        }

        try {
            return $this->checkUrlByType($url, $streamType, $startTime);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->handleConnectionException($e, $startTime);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return $this->handleRequestException($e, $startTime);
        } catch (\Exception $e) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_UNKNOWN,
                errorMessage: $e->getMessage(),
                responseTimeMs: $this->calculateResponseTime($startTime),
            );
        }
    }

    /**
     * Check stream based on its type.
     */
    protected function checkStreamByType(Stream $stream, string $url, float $startTime): array
    {
        return $this->checkUrlByType($url, $stream->stream_type, $startTime);
    }

    /**
     * Check URL based on stream type.
     */
    protected function checkUrlByType(string $url, string $streamType, float $startTime): array
    {
        return match ($streamType) {
            'hls' => $this->checkHlsStream($url, $startTime),
            'http' => $this->checkHttpStream($url, $startTime),
            'mpegts' => $this->checkMpegTsStream($url, $startTime),
            'rtmp' => $this->checkRtmpStream($url, $startTime),
            default => $this->checkGenericStream($url, $startTime),
        };
    }

    /**
     * Check HLS (m3u8) stream.
     */
    protected function checkHlsStream(string $url, float $startTime): array
    {
        $response = Http::timeout($this->timeout)->get($url);
        $responseTime = $this->calculateResponseTime($startTime);

        if (! $response->successful()) {
            return $this->buildHttpErrorResult($response->status(), $responseTime);
        }

        $contentType = $response->header('Content-Type');
        $body = $response->body();

        // Validate HLS content
        $isValidHls = str_contains($body, '#EXTM3U') ||
                      str_contains($contentType ?? '', 'mpegurl') ||
                      str_contains($contentType ?? '', 'x-mpegurl');

        if (! $isValidHls && strlen($body) > 0) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_INVALID_FORMAT,
                errorMessage: 'Response is not valid HLS/M3U8 format',
                httpStatus: $response->status(),
                responseTimeMs: $responseTime,
                contentType: $contentType,
            );
        }

        return $this->buildResult(
            status: 'online',
            httpStatus: $response->status(),
            responseTimeMs: $responseTime,
            contentType: $contentType,
        );
    }

    /**
     * Check HTTP direct stream.
     */
    protected function checkHttpStream(string $url, float $startTime): array
    {
        $response = Http::timeout($this->timeout)->head($url);
        $responseTime = $this->calculateResponseTime($startTime);

        if (! $response->successful()) {
            return $this->buildHttpErrorResult($response->status(), $responseTime);
        }

        return $this->buildResult(
            status: 'online',
            httpStatus: $response->status(),
            responseTimeMs: $responseTime,
            contentType: $response->header('Content-Type'),
        );
    }

    /**
     * Check MPEG-TS stream.
     */
    protected function checkMpegTsStream(string $url, float $startTime): array
    {
        $response = Http::timeout($this->timeout)->head($url);
        $responseTime = $this->calculateResponseTime($startTime);

        if (! $response->successful()) {
            return $this->buildHttpErrorResult($response->status(), $responseTime);
        }

        return $this->buildResult(
            status: 'online',
            httpStatus: $response->status(),
            responseTimeMs: $responseTime,
            contentType: $response->header('Content-Type'),
        );
    }

    /**
     * Check RTMP stream (URL validation only).
     */
    protected function checkRtmpStream(string $url, float $startTime): array
    {
        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['scheme'], $parsed['host'])) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_INVALID_URL,
                errorMessage: 'Invalid RTMP URL structure',
                responseTimeMs: $this->calculateResponseTime($startTime),
            );
        }

        if (! in_array($parsed['scheme'], ['rtmp', 'rtmps'])) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_INVALID_URL,
                errorMessage: 'URL scheme must be rtmp or rtmps',
                responseTimeMs: $this->calculateResponseTime($startTime),
            );
        }

        // For RTMP, we can only validate the URL structure
        // Full connectivity check would require ffprobe or similar
        return $this->buildResult(
            status: 'online',
            responseTimeMs: $this->calculateResponseTime($startTime),
        );
    }

    /**
     * Check generic stream with HEAD request.
     */
    protected function checkGenericStream(string $url, float $startTime): array
    {
        $response = Http::timeout($this->timeout)->head($url);
        $responseTime = $this->calculateResponseTime($startTime);

        if (! $response->successful()) {
            return $this->buildHttpErrorResult($response->status(), $responseTime);
        }

        return $this->buildResult(
            status: 'online',
            httpStatus: $response->status(),
            responseTimeMs: $responseTime,
            contentType: $response->header('Content-Type'),
        );
    }

    /**
     * Handle connection exceptions.
     */
    protected function handleConnectionException(\Illuminate\Http\Client\ConnectionException $e, float $startTime): array
    {
        $message = $e->getMessage();
        $responseTime = $this->calculateResponseTime($startTime);

        // Determine specific error type
        if (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_TIMEOUT,
                errorMessage: 'Connection timed out',
                responseTimeMs: $responseTime,
            );
        }

        if (str_contains($message, 'Could not resolve host') || str_contains($message, 'getaddrinfo')) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_DNS,
                errorMessage: 'DNS resolution failed',
                responseTimeMs: $responseTime,
            );
        }

        if (str_contains($message, 'SSL') || str_contains($message, 'certificate')) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_SSL,
                errorMessage: 'SSL/TLS certificate error',
                responseTimeMs: $responseTime,
            );
        }

        return $this->buildResult(
            status: 'offline',
            errorType: self::ERROR_CONNECTION,
            errorMessage: 'Connection failed: '.$message,
            responseTimeMs: $responseTime,
        );
    }

    /**
     * Handle HTTP request exceptions.
     */
    protected function handleRequestException(\Illuminate\Http\Client\RequestException $e, float $startTime): array
    {
        $responseTime = $this->calculateResponseTime($startTime);
        $status = $e->response?->status();

        return $this->buildHttpErrorResult($status ?? 0, $responseTime);
    }

    /**
     * Build result for HTTP errors.
     */
    protected function buildHttpErrorResult(int $status, int $responseTime): array
    {
        if ($status >= 400 && $status < 500) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_HTTP_4XX,
                errorMessage: "HTTP {$status}: Client error",
                httpStatus: $status,
                responseTimeMs: $responseTime,
            );
        }

        if ($status >= 500) {
            return $this->buildResult(
                status: 'offline',
                errorType: self::ERROR_HTTP_5XX,
                errorMessage: "HTTP {$status}: Server error",
                httpStatus: $status,
                responseTimeMs: $responseTime,
            );
        }

        return $this->buildResult(
            status: 'offline',
            errorType: self::ERROR_UNKNOWN,
            errorMessage: "HTTP {$status}: Unexpected response",
            httpStatus: $status,
            responseTimeMs: $responseTime,
        );
    }

    /**
     * Build a result array.
     */
    protected function buildResult(
        string $status,
        ?string $errorType = null,
        ?string $errorMessage = null,
        ?int $httpStatus = null,
        ?int $responseTimeMs = null,
        ?string $contentType = null,
    ): array {
        return [
            'status' => $status,
            'is_online' => $status === 'online',
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'http_status' => $httpStatus,
            'response_time_ms' => $responseTimeMs,
            'content_type' => $contentType,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if a URL is valid.
     */
    protected function isValidUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parsed = parse_url($url);

        if ($parsed === false) {
            return false;
        }

        $validSchemes = ['http', 'https', 'rtmp', 'rtmps'];

        return isset($parsed['scheme'], $parsed['host']) &&
               in_array($parsed['scheme'], $validSchemes);
    }

    /**
     * Calculate response time in milliseconds.
     */
    protected function calculateResponseTime(float $startTime): int
    {
        return (int) round((microtime(true) - $startTime) * 1000);
    }

    /**
     * Get human-readable error description.
     */
    public static function getErrorDescription(?string $errorType): string
    {
        return match ($errorType) {
            self::ERROR_TIMEOUT => 'The stream took too long to respond. Check if the server is overloaded or network is slow.',
            self::ERROR_CONNECTION => 'Could not establish a connection to the stream server.',
            self::ERROR_DNS => 'Could not resolve the hostname. Check if the URL is correct.',
            self::ERROR_SSL => 'SSL/TLS certificate error. The server certificate may be invalid or expired.',
            self::ERROR_HTTP_4XX => 'Client error (4xx). Check credentials, URL path, or permissions.',
            self::ERROR_HTTP_5XX => 'Server error (5xx). The streaming server may be having issues.',
            self::ERROR_INVALID_URL => 'The stream URL is invalid or malformed.',
            self::ERROR_INVALID_FORMAT => 'The response is not in the expected stream format.',
            self::ERROR_UNKNOWN => 'An unexpected error occurred.',
            default => 'Stream is working correctly.',
        };
    }
}
