<?php
namespace App\Modules\Users\Controllers\Site\Account;

use Illuminate\Http\Request;
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