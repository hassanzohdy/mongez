<?php
namespace HZ\Illuminate\Mongez\Exceptions\Services\Payments;

use HZ\Illuminate\Mongez\Contracts\Services\Payments\PaymentGatewayResponse;

class InvalidPaymentException extends \Exception 
{
    /**
     * Payment Response
     * 
     * @var PaymentGatewayResponse
     */
    protected $response;

    /**
     * Constructor
     * 
     * @param  PaymentGatewayResponse $response
     */
    public function __construct(PaymentGatewayResponse $response)
    {
        $this->response = $response;

        parent::__construct($this->response->getMessage());
    }

    /**
     * Get response
     * 
     * @return PaymentGatewayResponse
     */
    public function response(): PaymentGatewayResponse
    {
        return $this->response;
    }
}