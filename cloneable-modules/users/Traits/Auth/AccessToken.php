<?php
namespace App\Modules\Users\Traits\Auth;

use Illuminate\Support\Str;

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

        $token = [
            'token' => $accessToken,
            'userAgent' => $request->userAgent(),
        ];

        if (empty($user->accessTokens)) {
            $user->accessTokens = [$token];
        } else {
            $accessTokens = $user->accessTokens;
            array_push($accessTokens, $token);
            $user->accessTokens = $accessTokens;
        }

        $user->save();

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
        $model = static::MODEL;
        
        $user =  $model::where('accessTokens.token', $accessToken)
                       ->where('accessTokens.userAgent', request()->userAgent())->first();
        
        return $user ?: null;
    }
}