<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

trait WithAccessToken
{
    /**
     * Generated Access Token
     * 
     * @var string
     */
    protected static string $accessToken = '';

    /**
     * Get api key
     * 
     * @var string
     */
    protected static string $apiKey = '';

    /**
     * Get access token
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        if (static::$accessToken) return static::$accessToken;

        $accessToken = $this->accessTokenSettings();

        $accessTokenResponseKeyPath = $accessToken['tokenResponseKey'] ?? 'accessToken';

        $this->isAuthenticated = false;

        $response = $this->post($accessToken['route'], $accessToken['body'] ?? [], $accessToken['headers'] ?? []);

        $this->instantMessage('Generating Access Token...', 'cyan');

        static::$accessToken = Arr::get($response->toArray(), $accessTokenResponseKeyPath);
        $this->isAuthenticated = true;

        $this->instantMessage('Access Token Has been generated successfully...', 'green');

        return static::$accessToken;
    }

    /**
     * Get api key
     * 
     * @return string
     */
    protected function getApiKey(): string
    {
        return static::$apiKey;
    }

    /**
     * Get access token settings
     * 
     * @return array
     */
    protected function accessTokenSettings(): array
    {
        return config('mongez.testing.accessToken');
    }
}
