<?php
namespace App\Http\Controllers\Api\Site\Account;

use Request;
use Validator;
use ApiController;

class MeController extends ApiController
{
    /**
     * Create new users
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $usersRepository = repo(config('app.user-repo'));
        return $this->success([
            'user' => $usersRepository->wrap(user()),
        ]);
    }
}