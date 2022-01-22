<?php

namespace HZ\Illuminate\Mongez\Contracts\Users;

interface UserAccountTypeContract
{
    /**
     * Get current user account type such as user | admin | customer ...etc
     * 
     * @return string 
     */
    public function getAccountType(): string;

    /**
     * Determine whether the current account type is the given type
     * 
     * @param  string $accountType
     * @return bool
     */
    public function accountIs(string $accountType): bool;
}
