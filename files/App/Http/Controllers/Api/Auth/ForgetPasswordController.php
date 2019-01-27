<?php
namespace App\Http\Controllers\Api\Auth;

use Request;
use ApiController;

class ForgetPasswordController extends ApiController
{    
    /**
     * Send an email to reset password
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        //
        return $this->success();
    }
}