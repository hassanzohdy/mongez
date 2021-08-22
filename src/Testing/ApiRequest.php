<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;

class apiRequest
{
    /**
     * Response object
     * 
     * @var \Illuminate\Testing\TestResponse
     */
    protected TestResponse $response;

    /**
     * Request Body
     * 
     * @var array
     */
    protected array $requestBody;

    /**
     * Request method
     * 
     * @var string
     */
    protected string $requestMethod;

    /**
     * Request route
     * 
     * @var string
     */
    protected string $route;

    /**
     * Response shape
     * 
     * @var array
     */
    protected array $setResponseShape;

    /**
     * Set response object
     * 
     * @param  \Illuminate\Testing\TestResponse $response
     * @return $this
     */
    public function setResponse(TestResponse $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Set response shape
     * 
     * @param  array $responseShape
     * @return $this
     */
    public function setResponseShape(array $setResponseShape): self
    {
        $this->setResponseShape = $setResponseShape;
        return $this;
    }

    /**
     * Set request route
     * 
     * @param  string $route
     * @return $this
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set request method
     * 
     * @param  string $requestMethod
     * @return $this
     */
    public function setRequestMethod(string $requestMethod): self
    {
        $this->requestMethod = $requestMethod;
        return $this;
    }

    /**
     * Set request body
     * 
     * @param  array $requestBody
     * @return $this
     */
    public function setRequestBody(array $requestBody): self
    {
        $this->requestBody = $requestBody;
        return $this;
    }

    /**
     * Get response code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Get request route
     * 
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get request method
     * 
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Get request body
     * 
     * @return array
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * Get response object
     * 
     * @return \Illuminate\Testing\TestResponse
     */
    public function getResponse(): TestResponse
    {
        return $this->response;
    }

    /**
     * Get response body
     * 
     * @return mixed
     */
    public function getResponseBody()
    {
        return $this->response->getContent();
    }

    /**
     * Get response as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return json_decode($this->response->decodeResponseJson()->json, true);
    }

    /**
     * Get response as object
     * 
     * @return object
     */
    public function toObject(): object
    {
        return json_decode($this->response->decodeResponseJson()->json);
    }

    /**
     * Try to get last insert id
     * 
     * @return int
     */
    public function getLastInsertId(): int
    {
        return (int) Arr::get($this->toArray(), 'data.record.id');
    }
}
