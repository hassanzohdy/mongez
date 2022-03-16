<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Traits;

use Illuminate\Support\Arr;
use HZ\Illuminate\Mongez\Testing\apiRequest;
use Illuminate\Testing\Fluent\AssertableJson;

trait WithCreatingRequests
{
    /**
     * Create fail create request test
     * 
     * @param array $data
     * @param array $errorKeys
     * @param bool $ignoreOtherKeys | if set to true, it will ignore other keys
     * @return ApiRequest
     */
    protected function assertFailCreate(array $data, array $errorKeys, bool $ignoreOtherKeys = false)
    {
        return $this->assertBadRequest($this->getRoute(), 'POST', $data, $errorKeys, $ignoreOtherKeys);
    }

    /**
     * Create success create request
     * 
     * @param   array $data
     * @param   array $responseRecordShape 
     * @return  void
     */
    protected function successCreate(array $data, array $responseRecordShape)
    {
        $response = $this->post($this->getRoute(), $data);

        if ($response->getStatusCode() === 500) {
            dd($response->getContent());
        } else {
            $response->assertStatus(200);
        }

        $response->assertJson(function (AssertableJson $json) use ($responseRecordShape) {
            $data = Arr::dot($responseRecordShape);

            foreach ($data as $key => $type) {
                $json->whereType('data.record.' . $key, $type);
            }

            $json->etc();
        });

        $this->response = $response;

        return (new apiRequest())
            ->setResponse($response)
            ->setRequestBody($data)
            ->setRoute($this->getRoute())
            ->setResponseShape($responseRecordShape)
            ->setRequestMethod('POST');
    }

    /**
     * Create successful record
     * 
     * @return RequestApi
     */
    protected function createSuccessfulRecord()
    {
        return $this->successCreate($this->fullData(), $this->recordShape());
    }
}
