<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use Illuminate\Support\Str;
use Tests\CreatesApplication;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\LoggedExceptionCollection;
use HZ\Illuminate\Mongez\Testing\Traits\Messageable;
use HZ\Illuminate\Mongez\Testing\Traits\WithAccessToken;
// use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class ApiTestCase extends TestCase
{
    // use CreatesApplication;

    // use WithFaker;

    use WithAccessToken;

    use Messageable;

    // use RefreshDatabase;

    /**
     * Repository name
     * 
     * @const string
     */
    protected const REPOSITORY_NAME = '';

    /**
     * If marked as true, a bearer token will be passed with Bearer in the Authorization Header
     * 
     * @var bool
     */
    protected ?bool $isAuthenticated = false;

    /**
     * Add Prefix to all routes
     * 
     * @var string
     */
    protected $apiPrefix = '/api';

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Call another test unit
     * 
     * @param  string $unitTestClass
     * @return  ApTestCase
     */
    public function callTest(string $unitTestClass)
    {
        $class = App::make($unitTestClass);

        $class->setUp();

        return $class;
    }

    /**
     * Make a call from another test and refresh the application
     * 
     * @param  mixed $response
     * @return mixed
     */
    protected function callFrom($response)
    {
        // $this->refreshApplication();

        return $response;
    }

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
     * Handle Authorization Header
     * 
     * @param array $headers
     * @return array
     */
    protected function handleAuthorizationHeader(array $headers): array
    {
        // merge headers with default config headers
        $headers = array_merge($this->appendHeaders(), $headers);

        if (empty($headers['Authorization']) && $this->isAuthenticated !== null) {
            $headers['Authorization'] = $this->isAuthenticated ? 'Bearer ' . $this->getAccessToken() : 'key ' . $this->getApiKey();
        }

        return $headers;
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function get($uri, array $headers = []): TestResponse
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::getJson($uri, $headers);
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::postJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::putJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function patch($uri, array $data = [], array $headers = [])
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::patchJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::deleteJson($uri, $data, $headers);
    }

    /**
     * Visit the given URI with an OPTIONS request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function options($uri, array $data = [], array $headers = [])
    {
        $headers = $this->handleAuthorizationHeader($headers);

        return parent::optionsJson($uri, $data, $headers);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $route
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string|null  $content
     * @return \HZ\Illuminate\Mongez\Testing\TestResponse
     */
    public function call($method, $route, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $uri = $this->prepareUri($route);

        $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);

        $response->setRoute($route);

        return $response;
    }

    // /**
    //  * Create the test response instance from the given response.
    //  *
    //  * @param  \Illuminate\Http\Response  $response
    //  * @return \Illuminate\Testing\TestResponse
    //  */
    // protected function createTestResponse($response, $request)
    // {
    //     // $testResponse = TestResponse::fromBaseResponse($response);

    //     // $testResponse->setTestSuit($this);

    //     // return tap($testResponse, function ($response) {
    //     //     $response->withExceptions(
    //     //         $this->app->bound(LoggedExceptionCollection::class)
    //     //             ? $this->app->make(LoggedExceptionCollection::class)
    //     //             : new LoggedExceptionCollection()
    //     //     );
    //     // });

    //     return $response;
    // }

    /**
     * Prepare the given uri
     * 
     * @param  string $uri
     * @return string
     */
    protected function prepareUri(string $uri): string
    {
        $uri = $this->apiPrefix . '/' . ltrim($uri, '/');

        // if (Str::contains($uri, '?')) {
        //     $uri .= '&';
        // } else {
        //     $uri .= '?';
        // }

        // $uri .= $this->isAuthenticated ? 'Token=' . $this->getAccessToken() : 'Key=' . env('API_KEY');

        return $uri;
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

                $data[$key] = \fake()->password($length);
            } else {
                $data[$key] = \fake()->$value;
            }
        }

        return $data;
    }

    /**
     * Append more headers to each request
     * 
     * @return array
     */
    public function appendHeaders(): array
    {
        return \config('mongez.testing.headers', []);
    }
}
