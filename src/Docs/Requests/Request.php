<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Requests;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Docs\Directory;
use HZ\Illuminate\Mongez\Docs\Nodes\Node;

class Request
{
    /**
     * Request Name
     * 
     * @const string
     */
    const REQUEST_NAME = '';

    /**
     * Request description
     * 
     * @const string
     */
    const REQUEST_DESCRIPTION = '';

    /**
     * Request Path
     * 
     * @const string
     */
    const REQUEST_PATH = '';

    /**
     * Request Method
     * 
     * @const string
     */
    const REQUEST_METHOD = '';

    /**
     * Request Version
     * 
     * @const string
     */
    const REQUEST_VERSION = '1.0.0';

    /**
     * Request Parent
     */
    protected Directory $parent;

    /**
     * Request Headers
     */
    protected array $headers = [];

    /**
     * Request Body
     */
    protected array $requestBody = [];

    /**
     * Responses List
     */
    protected array $responses = [];

    /**
     * Request Parameters
     */
    protected array $parameters = [];

    /**
     * Set Request Parent
     * 
     * @param  Directory $parent
     * @return self
     */
    public function setParent(Directory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Set Request Headers
     * 
     * @param  array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set Request Body
     *
     * @param  array $requestBody
     * @return self
     */
    public function setRequestBody(array $requestBody): self
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * Set Request Parameters
     * 
     * @param  array $parameters
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Set Responses List
     * 
     * @param  array $responses
     * @return self
     */
    public function setResponses(array $responses): self
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * Get Request Name
     * 
     * @return string
     */
    public function getName(): string
    {
        return static::REQUEST_NAME;
    }

    /**
     * Get Request Description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return static::REQUEST_DESCRIPTION;
    }

    /**
     * Get Request Path
     * 
     * @return string
     */
    public function getPath(): string
    {
        return static::REQUEST_PATH;
    }

    /**
     * Get Request Method
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return static::REQUEST_METHOD;
    }

    /**
     * Get Request Version
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return static::REQUEST_VERSION;
    }

    /**
     * Get Request Parent
     * 
     * @return Directory
     */

    public function getParent(): Directory
    {
        return $this->parent;
    }

    /**
     * Define request header
     * 
     * @return array
     */
    protected function header(string $header, string $value)
    {
        return compact('header', 'value');
    }

    /**
     * Get Request Headers
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get Request Body
     * 
     * @return array
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * Get Request Parameters
     * 
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get Responses List
     * 
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * {@inheritDoc}
     */
    public function parse(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'path' => $this->getPath(),
            'method' => $this->getMethod(),
            'version' => $this->getVersion(),
            'headers' => $this->getHeaders(),
            'body' => $this->getRequestBody(),
            'parameters' => $this->getParameters(),
            'responses' => $this->getResponses(),
        ];
    }

    /**
     * Generate new generic Node
     * 
     * @param  string $type
     * @param  string $name
     * @param  string $description
     * @return Node
     */
    public function node(string $type, string $name, string $description): Node
    {
        return (new Node($name, $description))->type($type);
    }

    /**
     * Call nodes dynamically
     * 
     * @param  string $method
     * @param  array $arguments
     * @return Node
     */
    public function __call(string $method, array $arguments): Node
    {
        if (Str::endsWith($method, 'Node')) {
            $nodeType = Str::beforeLast($method, 'Node');

            $node = conifg('mongez.docs.nodes.' . $nodeType);

            return new $node(...$arguments);
        }
    }
}
