<?php
namespace App\Modules\Users\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

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