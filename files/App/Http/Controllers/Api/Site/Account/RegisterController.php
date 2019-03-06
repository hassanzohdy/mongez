<?php
namespace App\Http\Controllers\Api\Site\Account;

use Auth;
use Request;
use Validator;
use ApiController;

class RegisterController extends ApiController
{
    /**
     * Create new users
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $validator = $this->scan($request);

        if ($validator->passes()) {            
            $usersRepository = $this->{config('app.user-repo')};
            $user = $usersRepository->create($request);

            $userInfo = $usersRepository->wrap($user)->toArray($request);
            $userInfo['accessToken'] = $user->accessTokens[0]['token'];

            return $this->success([
                'user' => $userInfo,
            ]);
        } else {
            return $this->badRequest($validator->errors());
        }
    }

    /**
     * Determine whether the passed values are valid
     *
     * @return mixed
     */
    private function scan(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:' . config('app.user-type'),
        ]);
    }
}