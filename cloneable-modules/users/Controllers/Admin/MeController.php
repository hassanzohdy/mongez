<?php
namespace App\Modules\Users\Controllers\Admin;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class MeController extends ApiController
{
    /**
     * User model object
     * 
     * @var \App\Modules\Users\Models\User 
     */
    protected $user;

    /**
     * Create new users
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $this->user = user();

        return $this->successUser();
    }

    /**
     * Return success response with user object
     * 
     * @return mixed
     */
    protected function successUser()
    {
        $usersRepository = repo('users');

        return $this->success([
            'user' => $usersRepository->wrap($this->user),
        ]);
    }
}