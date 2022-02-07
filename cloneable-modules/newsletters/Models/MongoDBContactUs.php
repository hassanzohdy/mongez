<?php

namespace App\Modules\NewsLetters\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class ContactUs extends Model
{

    /**
     * Get shared info for the user that will be stored as a sub document of another collection
     * 
     * @return array
     */
    public function sharedInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'subject' => $this->subject,
            'message' => $this->message,
        ];
    }
}
