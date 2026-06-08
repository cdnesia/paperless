<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $tokenCacheKey = 'cdnesia_access_token';

    public function __construct()
    {
        $this->baseUrl = config('services.cdnesia.base_url', 'https://api.cdnesia.com');
        $this->clientId = config('services.cdnesia.client_id');
        $this->clientSecret = config('services.cdnesia.client_secret');
    }

    /**
     * Get access token (with caching and refresh)
     */
    protected function getAccessToken()
    {
        $tokenData = Cache::get($this->tokenCacheKey);

        if ($tokenData && isset($tokenData['access_token'], $tokenData['expires_at'])) {
            if (time() < ($tokenData['expires_at'] - 60)) {
                return $tokenData['access_token'];
            }

            if (isset($tokenData['refresh_token'])) {
                $refreshed = $this->refreshToken($tokenData['refresh_token']);
                if ($refreshed) {
                    return $refreshed;
                }
            }
        }

        return $this->requestNewToken();
    }

    /**
     * Request new access token
     */
    protected function requestNewToken()
    {
        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->post("{$this->baseUrl}/oauth/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => '*'
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $tokenData = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'expires_at' => time() + ($data['expires_in'] ?? 3600),
                ];

                Cache::put($this->tokenCacheKey, $tokenData, now()->addSeconds($data['expires_in'] ?? 3600));

                return $data['access_token'];
            }

            Log::error('CDNesia Token Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('Failed to get access token: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('CDNesia Token Request Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Refresh access token
     */
    protected function refreshToken($refreshToken)
    {
        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->post("{$this->baseUrl}/oauth/token", [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => '*'
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update cache
                $tokenData = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                    'expires_at' => time() + ($data['expires_in'] ?? 3600),
                ];

                Cache::put($this->tokenCacheKey, $tokenData, now()->addSeconds($data['expires_in'] ?? 3600));

                return $data['access_token'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('CDNesia Token Refresh Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate signature for API request
     */
    protected function generateSignature($method, $path, $timestamp, $nonce, $body = '')
    {
        $bodyHash = hash('sha256', $body);
        $rawPayload = implode('|', [
            strtoupper($method),
            $path,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        return hash_hmac('sha256', $rawPayload, $this->clientSecret);
    }

    /**
     * Make API request
     */
    public function request($method, $path, $payload = null)
    {
        $accessToken = $this->getAccessToken();

        $isGet = strtoupper($method) === 'GET';
        $body = (!$isGet && $payload) ? json_encode($payload) : '';
        $timestamp = (string) time();
        $nonce = bin2hex(random_bytes(16));

        // Generate signature
        $signature = $this->generateSignature($method, $path, $timestamp, $nonce, $body);

        // Prepare headers
        $headers = [
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE' => $nonce,
            'X-SIGNATURE' => $signature,
            'X-CLIENT-SECRET' => $this->clientSecret,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->{strtolower($method)}("{$this->baseUrl}/{$path}", $payload ?? []);

            if ($response->failed()) {
                Log::error('CDNesia API Request Failed', [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return [
                'status' => $response->status(),
                'success' => $response->successful(),
                'data' => $response->json('data') ?? null,
                'message' => $response->json('message') ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('CDNesia API Request Exception', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 500,
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * GET request helper
     */
    public function get($path)
    {
        return $this->request('GET', $path);
    }

    /**
     * POST request helper
     */
    public function post($path, $payload)
    {
        return $this->request('POST', $path, $payload);
    }

    /**
     * PUT request helper
     */
    public function put($path, $payload)
    {
        return $this->request('PUT', $path, $payload);
    }

    /**
     * DELETE request helper
     */
    public function delete($path)
    {
        return $this->request('DELETE', $path);
    }
}
