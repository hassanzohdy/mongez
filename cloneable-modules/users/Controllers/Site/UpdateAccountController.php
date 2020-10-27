<?php

namespace App\Modules\Users\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use HZ\Illuminate\Mongez\Managers\ApiController;

class UpdateAccountController extends ApiController
{
    /**
     * Repository name
     * 
     * @var string
     */
    protected $repository = 'users';

    /**
     * {@inheritDoc}
     */
    public function index(Request $request)
    {
        $validator = $this->scan($request);

        $user = user();
        if (!$validator->passes()) {
            return $this->badRequest($validator->errors());
        } elseif ($request->password && !$user->isMatchingPassword($request->oldPassword)) {
            return $this->badRequest(trans('auth.invalidPassword'));
        } else {
            $user = $this->repository->update($user->id, $request);
        }

        return $this->success([
            $user->accountType() => $this->repository->wrap($user)
        ]);
    }

    /**
     * Determine whether the passed values are valid
     *
     * @return mixed
     */
    protected function scan(Request $request)
    {
        $user = user();

        $table = $this->repository->getTableName();

        return Validator::make($request->all(), [
            'name' => 'required|min:4',
            'password' => 'confirmed|min:8',
            'email' => [
                'required',                
                "unique:$table,email,{$user->email},email"
            ],
            'phoneNumber' => [
                'required',
                "unique:$table,phoneNumber,{$user->phoneNumber},phoneNumber"
            ],
        ]);
    }
}
