<?php

/**
 * This event is used to send user info with any response if the user is logged in
 */
namespace HZ\Illuminate\Mongez\Events;

class WithUser
{
    /**
     * {@inheritDoc}
     */
    public function sendUser($response)
    {
        $user = user();

        $userType = config('app.user-type', 'user');

        if ($user && empty($response[$userType])) {
            // @see App\Modules\Users\Middleware\Auth.php
            $repositoryName = config('app.users-repo', 'users');
            $resource = repo($repositoryName)->wrap($user->refresh());

            // just for now
            // $resource->disable('accessToken', 'accessTokens');
            // $resource->set('accessToken', $user->accessTokens[0]['token']);

            $response[$userType] = $resource->toArray(request());
        }

        return $response;
    }
}
