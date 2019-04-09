<?php
namespace App\Modules\Users\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use Validator;
use ApiController;

class ChangePasswordController extends ApiController
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
            $user = user();
            if (! $user->isMatchingPassword($request->oldPassword)) {
                return $this->badRequest([
                    'errors' => [
                        'oldPassword' => 'incorrect-old-password',
                    ],
                ]);
            }

            $user->updatePassword($request->newPassword);

            return $this->success([
                'success' => true,
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
            'newPassword' => 'required|min:8',
            'oldPassword' => 'required|min:8',
            'confirmPassword' => 'required|min:8',
        ]);
    }
}