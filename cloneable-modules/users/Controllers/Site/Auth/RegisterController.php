<?php

namespace App\Modules\Users\Controllers\Site\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use HZ\Illuminate\Mongez\Http\ApiController;

class RegisterController extends ApiController
{
    /**
     * Create new users
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $this->repository = $this->{config('app.users-repo')};

        $validator = $this->scan($request);

        if ($validator->passes()) {
            $user = $this->repository->create($request);
            $userInfo = $this->repository->wrap($user)->toArray($request);
            $userInfo['accessToken'] = $user->accessTokens[0]['token'];

            if ($request->device) {
                $user->addNewDeviceToken($request->device);
            }

            return $this->success([
                $user->accountType() => $userInfo,
            ]);
        } else {
            return $this->badRequest($validator->errors());
        }
    }

    /**
     * Determine whether the passed values are valid
     *
     * @param Request $request
     * @return mixed
     */
    protected function scan(Request $request)
    {
        $table = $this->repository->getTableName();

        return Validator::make($request->all(), [
            'name' => 'required|min:4',
            'password' => 'required|min:8',
            'phoneNumber' => 'required|unique:' . $table,
            'email' => 'required|unique:' . $table,
        ]);
    }
}
