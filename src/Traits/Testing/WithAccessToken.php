<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Traits\Testing;

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
     * Get access token
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        if (static::$accessToken) return static::$accessToken;

        $settings = $this->accessTokenSettings();

        $this->instantMessage('Generating Access Token...', 'yellow');

        $currentApiPrefix = $this->apiPrefix;

        $this->apiPrefix = $settings['apiPrefix'];
        $credentials = [];

        if (!empty($settings['credentials'])) {
            $credentials = $settings['credentials'];
        } elseif (isset($settings['createUser'])) {
            [$className, $method] = $settings['createUser'];
            $userCreator = App::make($className);
            $credentials = $userCreator->$method();
        }

        $response = $this->isAuthorized(false)->post($settings['loginRoute'], $credentials);

        $this->apiPrefix = $currentApiPrefix;
        $response = json_decode($response->getContent(), true);

        static::$accessToken = Arr::get($response, $settings['accessTokenResponseKey']);

        $this->instantMessage('Access Token Has Been Generated Successfully.', 'green');
        $this->instantMessage('Access Token Has Been Cached for rest of unit tests.', 'cyan');

        return static::$accessToken;
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
