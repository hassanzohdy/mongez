<?php

namespace App\Modules\Users\Controllers\Site\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use HZ\Illuminate\Mongez\Http\ApiController;

class LogoutController extends ApiController
{
    /**
     * Login the user
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = user();

        $accessTokens = $user->accessTokens;

        $currentAccessToken = $request->authorizationValue();

        if ($request->device) {
            $user->removeDeviceToken($request->device);
        }

        foreach ($accessTokens as $key => $accessToken) {
            if ($accessToken['token'] == $currentAccessToken) {
                unset($accessTokens[$key]);
                break;
            }
        }

        $user->accessTokens = array_values($accessTokens);

        $user->save();

        Auth::logout();

        return $this->success();
    }
}
