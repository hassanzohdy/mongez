<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Requests;


class GetRequest extends Request
{
    /**
     * Request Method
     * 
     * @const string
     */
    const REQUEST_METHOD = 'GET';


    /**
     * Get Request Headers
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            $this->header('Authorization', 'Bearer {{ accessToken }}'),
            $this->header('Accept', 'application/json'),
            $this->header('Content-Type', 'application/json'),
        ];
    }
}
