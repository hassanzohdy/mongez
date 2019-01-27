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

            return $this->success([
                'user' => $usersRepository->wrap($user),
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