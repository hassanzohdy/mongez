<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Traits\Testing\WithCreatingRequests;

abstract class BaseCreateAdminTest extends BaseCrudAdminTest
{
    use WithCreatingRequests;
}
