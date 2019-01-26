<?php
namespace App\Http\Controllers\Api\Auth;

use Request;
use ApiController;

class ResetPasswordController extends ApiController
{    
    /**
     * Verify user code
     *
     * @return mixed
     */
    public function index($code, Request $request)
    {
        //
        return $this->success();
    }
    
    /**
     * Reset user password
     *
     * @return mixed
     */
    public function reset($code, Request $request)
    {
        //
        return $this->success();
    }
}