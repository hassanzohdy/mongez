<?php
namespace App\Modules\Users\Traits\Auth;

use Illuminate\Support\Facades\Hash;

trait updatePassword 
{
    /**
     * Check if the given password is matching the current one
     * 
     * @param  string $password
     * @return bool
     */
    public function isMatchingPassword($password) 
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Update the current password with the new one
     * 
     * @param  string $newPassword
     * @return void
     */
    public function updatePassword($password)
    {
        $this->password = Hash::make($password);

        $this->save();
    }
}