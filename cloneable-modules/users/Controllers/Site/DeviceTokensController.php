<?php

namespace App\Modules\Users\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Http\ApiController;

class DeviceTokensController extends ApiController
{
    /**
     * Add new device token to current user
     * 
     * @param Request $request
     * @return Response
     */
    public function addDeviceToken(Request $request)
    {
        if ($request->device) {
            user()->addNewDeviceToken($request->device);
            return $this->success();
        } else {
            return $this->badRequest(trans('validation.required', 'device'));
        }
    }

    /**
     * Add new device token to current user
     * 
     * @param Request $request
     * @return Response
     */
    public function removeDeviceToken(Request $request)
    {
        if ($request->device) {
            user()->removeDeviceToken($request->device);
            return $this->success();
        } else {
            return $this->badRequest(trans('validation.required', 'device'));
        }
    }
}
