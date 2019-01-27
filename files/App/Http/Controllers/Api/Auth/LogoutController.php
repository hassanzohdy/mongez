<?php
namespace App\Http\Controllers\Api\Auth;

use Request;
use ApiController;

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

        foreach ($accessTokens as $key => $accessToken) {
            if ($accessToken['token'] == $currentAccessToken) {
                unset($accessTokens[$key]);
                break;
            }
        }

        $user->accessTokens = array_values($accessTokens);

        $user->save();

        return $this->success();
    }
}