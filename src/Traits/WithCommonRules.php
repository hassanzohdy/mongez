<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Traits;

trait WithCommonRules
{
    /**
     * Merge the given rules with the common rules between store and update
     *
     * @param array $rules
     * @return array
     */
    public function withCommonRules(array $rules = []): array
    {
        return array_merge(method_exists($this, 'commonRules') ? $this->commonRules() : [], $rules);
    }
}
