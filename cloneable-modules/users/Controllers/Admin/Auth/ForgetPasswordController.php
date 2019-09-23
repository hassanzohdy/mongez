<?php
namespace App\Modules\Users\Controllers\Admin\Auth;

use Mail;
use Validator;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class ForgetPasswordController extends ApiController
{    
    /**
     * Send an email to reset password
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $validator = $this->isValid($request);
        if ($validator->passes()) {
            $user = repo('users')->getBy('email', $request->email)->resource;
            $user->code = mt_rand(10000, 99999);
            $user->save();
            Mail::send([], [], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Reset password')
                // here comes what you want
                ->setBody("Your reset password code is: <strong>{$user->code}</strong>", 'text/html'); // assuming text/plain
            });
            return $this->success();              
        } else {
            return $this->badRequest($validator->errors());
        } 
    }

    /**
     * Determine whether the passed values are valid
     *
     * @return mixed
     */
    private function isValid(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => 'required|exists:users',
        ]);
    }
}