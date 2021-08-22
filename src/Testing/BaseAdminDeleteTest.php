<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Traits\Testing\WithCreatingRequests;

abstract class BaseAdminDeleteTest extends BaseCrudAdminTest
{
    use WithCreatingRequests;

    /**
     * Test Success Delete
     */
    public function testSuccessDelete()
    {
        $request = $this->createSuccessfulRecord();

        $this->assertSuccessDelete($request->getLastInsertId());
    }

    /**
     * Test Not Found record
     */
    public function testNotFoundRecord()
    {
        $this->assertSuccessNotFoundRecord(-1);
    }

    /**
     * Create success Delete request
     * 
     * @param   int $id
     * @return  void
     */
    protected function assertSuccessDelete(int $id)
    {
        $response = $this->delete($this->getRoute() . '/' . $id);

        $response->assertStatus(200);
    }

    /**
     * Check success not found record
     * 
     * @param  int $id
     * @return  void
     */
    protected function assertSuccessNotFoundRecord(int $id)
    {
        $response = $this->get($this->getRoute() . '/' . $id);

        $response->assertStatus(404);
    }
}
