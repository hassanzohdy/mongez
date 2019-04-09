<?php
namespace App\Modules\Users\Traits\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

trait AccessToken 
{
    /**
     * Generate new access token to the given user model
     * 
     * @param  \App\Models\User\User $user
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function generateAccessToken($user, Request $request)
    {
        $accessToken = Str::random(96);

        $token = [
            'token' => $accessToken,
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
     * @return \App\Models\User\User
     */
    public function getByAccessToken(string $accessToken)
    {
        $model = static::MODEL;
        $user =  $model::where('accessTokens.token', $accessToken)->first();

        return $user ?: null;
    }
}