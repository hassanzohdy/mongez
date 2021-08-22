<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Traits\Testing\WithCreatingRequests;

abstract class BaseUpdateAdminTest extends AdminApiTestCase
{
    use WithCreatingRequests;

    /**
     * Create full success update
     * 
     * @return void
     */
    protected function createSuccessfulUpdate()
    {
        $request = $this->createSuccessfulRecord();

        $id = $request->getLastInsertId();

        $data = $this->fullData();

        $response = $this->put($this->getRoute() . '/' . $id, $data);

        $response->assertStatus(200);

        $this->deleteRecord($id);
    }


    /**
     * Create fail create request test
     * 
     * @param array|callback $data
     * @param array $errorKeys
     * @param bool $ignoreOtherKeys | if set to true, it will ignore other keys
     * @return ApiRequest
     */
    protected function assertFailUpdate($data, array $errorKeys, bool $ignoreOtherKeys = false)
    {
        $apiRequest = $this->createSuccessfulRecord();

        if (is_callable($data)) {
            $data = $data($apiRequest);
        }

        $failRequest = $this->assertBadRequest($this->getRoute() . '/' . $apiRequest->getLastInsertId(), 'PUT', $data, $errorKeys, $ignoreOtherKeys);

        $this->deleteRecord($apiRequest->getLastInsertId());

        return $failRequest;
    }
}
