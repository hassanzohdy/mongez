<?php
/**
 * This event is used to send user info with any response if the user is logged in
 */
namespace App\Modules\Users\Events;

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
            $resource = repo($repositoryName)->wrap($user);

            // just for now
            // $resource->disable('accessToken', 'accessTokens');
            $resource->set('accessToken', $user->accessTokens[0]['token']);

            $response[$user->accountType()] = $resource->toArray(request());
        }

        return $response;
    }
}
