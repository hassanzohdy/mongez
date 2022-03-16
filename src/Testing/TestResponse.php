<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Testing\Traits\Messageable;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse as BaseTestResponse;
use PHPUnit\TextUI\XmlConfiguration\PHPUnit;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class TestResponse extends BaseTestResponse
{
    use Messageable;

    /**
     * Response object
     * 
     * @var \Illuminate\Testing\TestResponse
     */
    protected $response;

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
     * Test case
     * 
     * @var TestCase
     */
    protected $testCase;

    /**
     * Response body
     * 
     * @var object
     */
    protected $responseBody;

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
     * @return \Illuminate\Http\Response
     */
    public function getResponse(): Response
    {
        return $this->baseResponse;
    }

    /**
     * Get response status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->baseResponse->getStatusCode();
    }

    /**
     * Set test case
     * 
     * @param  TestCase $testCase
     * @return void 
     */
    public function setTestSuit(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * Get response status code
     * 
     * @return int
     */
    public function statusCode(): int
    {
        return $this->baseResponse->getStatusCode();
    }

    /**
     * Get response body
     * 
     * @return mixed
     */
    public function getResponseBody()
    {
        return json_decode($this->baseResponse->getContent());
    }

    /**
     * Get response body
     * 
     * @return mixed
     */
    public function body()
    {
        return json_decode($this->baseResponse->getContent());
    }

    /**
     * Get response as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return json_decode($this->baseResponse->getContent(), true);
    }

    /**
     * Get response as object
     * 
     * @return object
     */
    public function toObject(): object
    {
        return $this->responseBody;
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

    /**
     * Assert success response
     * 
     * @return $this
     */
    public function assertSuccess()
    {
        return $this->assertStatus(HttpFoundationResponse::HTTP_OK);
    }

    /**
     * Assert success create response
     * 
     * @return $this
     */
    public function assertSuccessCreate()
    {
        return $this->assertStatus(HttpFoundationResponse::HTTP_CREATED);
    }

    /**
     * Assert bad request response
     * 
     * @return $this
     */
    public function assertBadRequest()
    {
        return $this->assertStatus(HttpFoundationResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Assert not found response
     * 
     * @return $this
     */
    public function assertNotFound()
    {
        return $this->assertStatus(HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * Assert unauthorized
     * 
     * @return $this
     */
    public function assertUnauthorized()
    {
        return $this->assertStatus(HttpFoundationResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Assert the current response to be the given response schema
     * 
     * @param  ResponseSchemaIterface $responseSchema
     * @return $this
     */
    public function assertResponse(ResponseSchemaInterface $responseSchema)
    {
        $responseSchema->setValue($this->toArray())->validate();

        if (!$responseSchema->isValid()) {
            $errors = (new ErrorsMessagesParser($responseSchema->errorsList()))->parse();

            $message = $this->color('Response Schema Failed:', 'red', ['bold']) . PHP_EOL;

            foreach ($errors as $error) {
                $message .= $error . PHP_EOL;
            }

            $this->testCase->fail($message);
        }

        return $this;
    }
}
