<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Traits;

use Symfony\Component\Console\Color;
use HZ\Illuminate\Mongez\Helpers\Testing\Message;

trait StrictUnit
{
    /**
     * Determine if response must be strict to the gievn schema
     * 
     * @var bool
     */
    protected $isStrict = null;

    /**
     * Determine if the response schema must be strict
     * 
     * @param  bool $isStrict
     * @return self
     */
    public function strict(bool $isStrict): self
    {
        $this->isStrict = $isStrict;

        return $this;
    }

    /**
     * Check if the unit has determined whther to be strict or not
     * 
     * If the value is null, then it is not defined
     */
    public function hasDeterminedIfStrict(): bool
    {
        return null !== $this->isStrict;
    }
}
