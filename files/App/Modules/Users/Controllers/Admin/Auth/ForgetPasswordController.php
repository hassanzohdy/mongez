<?php
namespace App\Modules\Users\Controllers\Admin\Auth;

use Illuminate\Http\Request;
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