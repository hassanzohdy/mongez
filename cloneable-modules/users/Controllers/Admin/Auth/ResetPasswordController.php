<?php
namespace App\Modules\Users\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class ResetPasswordController extends ApiController
{    
    /**
     * Verify user code
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $usersRepository = repo('users');
        $user = $usersRepository->getBy('email', $request->email);

        if (! $user || $user->resource->code != $request->code) {
            return $this->badRequest([
                'error' => 'Invalid code or email',
            ]);
        } 

        $user = $user->resource;

        unset($user->code);

        $user->updatePassword($request->password);

        $accessToken = $usersRepository->generateAccessToken($user, $request);

        $userInfo = $usersRepository->wrap($user)->toArray($request);
        
        $userInfo['accessToken'] = $accessToken;

        return $this->success([
            'user' => $userInfo,
        ]);
    }
}