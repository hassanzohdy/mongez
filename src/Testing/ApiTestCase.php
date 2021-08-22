<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use Illuminate\Support\Str;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use HZ\Illuminate\Mongez\Traits\Testing\WithRepository;

abstract class ApiTestCase extends TestCase
{
    use CreatesApplication;

    use WithFaker;

    use WithRepository;

    // use RefreshDatabase;

    /**
     * If marked as true, a bearer token will be passed with Bearer in the Authorization Header
     * 
     * @var bool
     */
    protected $isAuthenticated = false;

    /**
     * Add Prefix to all routes
     * 
     * @var string
     */
    protected $apiPrefix = '/api';

    /**
     * Response Object
     * 
     * @var \Illuminate\Testing\TestResponse
     */
    protected $response;

    /**
     * Mark the request as authorized request
     * 
     * @param bool $isAuthenticated 
     * @return $this
     */
    public function isAuthorized(bool $isAuthenticated = true): self
    {
        $this->isAuthenticated = $isAuthenticated;

        return $this;
    }

    /**
     * Assert bad request
     * 
     * @param  string $route
     * @param  string $requestMethod
     * @param array $errorKeys
     * @param bool $ignoreOtherKeys | if set to true, it will ignore other keys
     * @param  array $data
     */
    protected function assertBadRequest(string $route, string $requestMethod, array $data, array $errorKeys, bool $ignoreOtherKeys = false)
    {
        $response = $this->$requestMethod($route, $data);

        $apiRequest = new apiRequest();

        $apiRequest->setResponse($response)
            ->setRequestMethod($requestMethod)
            ->setRoute($route)
            ->setRequestBody($data);

        $jsonResponse = $apiRequest->toObject();

        $response->assertStatus(400);

        $this->assertObjectHasAttribute('errors', $jsonResponse);

        $errors = $jsonResponse->errors;

        $this->assertIsArray($errors);

        $responseErrorKeys = [];

        foreach ($errors as $error) {
            // check for the key property to be exists
            $this->assertObjectHasAttribute('key', $error);
            $this->assertObjectHasAttribute('value', $error);
            // check if the error key is listed 
            $errorKeyMustBeNotListed = in_array($error->key, $errorKeys);

            if ($ignoreOtherKeys === false) {
                $this->assertTrue($errorKeyMustBeNotListed, ($error->key) . ' error key asserted to be not exist, Error message for the key:' . $error->value);
            }

            $responseErrorKeys[] = $error->key;
        }

        foreach ($errorKeys as $errorKey) {
            $this->assertTrue(in_array($errorKey, $responseErrorKeys), ($errorKey) . ' error key asserted to be exist');
        }

        return $apiRequest;
    }

    /**
     * Handle Authorization Header
     * 
     * @param array $headers
     * @return void
     */
    protected function handleAuthorizationHeader(array &$headers)
    {
        if (!empty($headers['Authorization'])) return;

        $headers['Authorization'] = $this->isAuthenticated ? 'Bearer ' . env('BEARER_TOKEN') : 'key ' . env('API_KEY');
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function get($uri, array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::getJson($uri, $headers);
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::postJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::putJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function patch($uri, array $data = [], array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::patchJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::deleteJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with an OPTIONS request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function options($uri, array $data = [], array $headers = [])
    {
        $this->handleAuthorizationHeader($headers);

        return parent::optionsJson($uri, $data, $headers);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string|null  $content
     * @return \Illuminate\Testing\TestResponse
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $uri = $this->prepareUri($uri);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * Prepare the given uri
     * 
     * @param  string $uri
     * @return string
     */
    protected function prepareUri(string $uri): string
    {
        $uri = $this->apiPrefix . '/' . ltrim($uri, '/');

        if (Str::contains($uri, '?')) {
            $uri .= '&';
        } else {
            $uri .= '?';
        }

        $uri .= $this->isAuthenticated ? 'Token=' . env('BEARER_TOKEN') : 'Key=' . env('API_KEY');

        return $uri;
    }

    /**
     * Assert success json response
     * 
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertSuccess(array $structure, $responseData = null)
    {
        $this->response->assertStatus(200);

        $this->response->assertJsonStructure($structure, $responseData);

        return $this;
    }

    /**
     * Generate data for the given keys and return corresponding data
     * 
     * @param array $filling
     * @return array
     */
    protected function fill(array $filling)
    {
        $data = [];

        foreach ($filling as $key => $value) {
            if (!is_numeric($key)) {
                $key = $value;
                $data[$key] = $value;
                continue;
            }

            if (Str::contains('password', $key)) {
                $length = null;
                if (Str::contains($key, ':')) {
                    [$key, $length] = explode(':', $key);
                }

                $data[$key] = $this->faker->password($length);
            } else {
                $data[$key] = $this->faker->$value;
            }
        }

        return $data;
    }
}
