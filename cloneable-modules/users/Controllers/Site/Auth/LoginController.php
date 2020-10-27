<?php

namespace App\Modules\Users\Controllers\Site\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use HZ\Illuminate\Mongez\Managers\ApiController;

class LoginController extends ApiController
{
    /**
     * Login the user
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $validator = $this->scan($request);

        if ($validator->passes()) {
            $repository = repo(config('app.users-repo'));

            if (!($user = $repository->login($request))) {
                return $this->unauthorized(trans('auth.invalidData'));
            }

            if ($request->device) {
                $user->resource->addNewDeviceToken($request->device);
            }

            return $this->success([
                $user->resource->accountType() => $user,
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
    protected function scan(Request $request)
    {
        return Validator::make($request->all(), [
            'emailOrPhone' => 'required',
            'password' => 'required|min:8',
        ]);
    }
}
