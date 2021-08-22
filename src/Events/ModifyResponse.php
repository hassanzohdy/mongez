<?php

namespace HZ\Illuminate\Mongez\Events;

class ModifyResponse
{
    /**
     * {@inheritDoc}
     */
    public function modifyResponse($response, $statusCode)
    {
        if ($statusCode == 200) {
            $response = [
                'data' => $response,
            ];
        }

        return $response;
    }
}
