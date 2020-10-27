<?php

namespace HZ\Illuminate\Mongez\Services\Payments\HyperPay;

use Illuminate\Support\Arr;
use HZ\Illuminate\Mongez\Contracts\Services\Payments\PaymentGatewayResponse;

class HyperPayResponse implements PaymentGatewayResponse
{
    /**
     * Hyper pay main statuses codes list
     * 
     * @const string
     */
    const PENDING_TRANSACTION = '000.200.000';
    const SUCCESS_TRANSACTION = '000.000.000';
    const TEST_SUCCESS_TRANSACTION = ['000.100.112', '000.100.110'];

    /**
     * Response info
     * 
     * @var array
     */
    private $responseData = [
        // sample of array contents
        'statusCode' => 'transaction status code',
        'message' => 'transaction message',
        'response' => 'full response',
        'responseStatusCode' => 'response status code',
    ];

    /**
     * Constructor
     * 
     * @param array $responseData
     */
    public function __construct(array $responseData)
    {
        $this->responseData = $responseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): string
    {
        switch ($statusCode = $this->getStatusCode()) {
            case static::PENDING_TRANSACTION:
                return PaymentGatewayResponse::PENDING;
            case static::SUCCESS_TRANSACTION:
                return PaymentGatewayResponse::COMPLETED;
            default:
                if (in_array($statusCode, static::TEST_SUCCESS_TRANSACTION)) {
                    return PaymentGatewayResponse::COMPLETED;
                }

                return PaymentGatewayResponse::FAILED;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): string
    {
        return $this->responseData['statusCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function isPending(): bool
    {
        return $this->getStatusCode() === static::PENDING_TRANSACTION;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompleted(): bool
    {
        return in_array($this->getStatusCode(), array_merge([static::SUCCESS_TRANSACTION], static::TEST_SUCCESS_TRANSACTION));
    }

    /**
     * {@inheritdoc}
     */
    public function hasFailed(): bool
    {
        return !$this->isPending() && !$this->isCompleted();
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return $this->responseData['message'];
    }

    /**
     * Get full response
     * 
     * @return mixed
     */
    public function getResponse()
    {
        return $this->responseData['response'];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        return Arr::get($this->responseData['response'], $key);
    }
}
