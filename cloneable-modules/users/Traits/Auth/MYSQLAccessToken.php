<?php
namespace App\Modules\Users\Traits\Auth;

use Illuminate\Support\Str;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserToken;

trait AccessToken 
{
    /**
     * Generate new access token to the given user model
     * 
     * @param  \App\Modules\Users\Models\User $user
     * @param  |Illuminate\Http\Request $request
     * @return string
     */
    public function generateAccessToken($user, $request)
    {        
        $accessToken = Str::random(96);

        $userTokenModel = new UserToken;

        $userTokenModel->user_id = $user->id;

        $userTokenModel->token = $accessToken;

        $userTokenModel->save();

        return $accessToken;
    }

    /**
     * Get user model by access token
     * 
     * @param  string $accessToken
     * @return \App\Modules\Users\Models\User
     */
    public function getByAccessToken(string $accessToken)
    {
        $model = new UserToken;
        
        $accessTokenOfUser =  $model::where('token', $accessToken)->first();
        
        return $accessTokenOfUser ? User::find($accessTokenOfUser->user_id)->first() : null;  
    }
}