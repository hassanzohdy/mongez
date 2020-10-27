<?php

namespace App\Modules\Users\Traits;

trait Deviceable
{
    /**
     * Add new device to user 
     * Device options contains: type: ios|android, token: string 
     * 
     * @param  array $deviceOptions
     * @return void
     */
    public function addNewDeviceToken(array $deviceOptions)
    {
        if ($this->getDeviceToken($deviceOptions)) return;

        $deviceTokenModel = static::DEVICE_TOKEN_MODEL;

        $deviceToken = new $deviceTokenModel([
            'userId' => $this->id,
            'type' => $deviceOptions['type'], // ios | android
            'token' => $deviceOptions['token'],
        ]);

        $deviceToken->save();

        $this->associate($deviceToken, 'devices')->save();
    }

    /**
     * Remove device from user 
     * 
     * @param  array $deviceOptions
     * @return void
     */
    public function removeDeviceToken(array $deviceOptions)
    {
        $deviceToken = $this->getDeviceToken($deviceOptions);

        if (!$deviceToken) return;

        $this->disassociate($deviceToken, 'devices')->save();

        $deviceToken->delete();
    }

    /**
     * Get device token for the given user and device options
     * 
     * @param array $deviceOptions
     * @return DeviceToken
     */
    public function getDeviceToken(array $deviceOptions)
    {
        $deviceTokenModel = static::DEVICE_TOKEN_MODEL;

        return $deviceTokenModel::where('token', $deviceOptions['token'])->where('userId', $this->id)->where('type', $deviceOptions['type'])->first();
    }

    /**
     * Get user devices ids for firebase
     * 
     * @return array
     */
    public function getFireBaseDevicesIds(): array
    {
        return collect($this->devices)->pluck('token')->toArray();
    }
}
