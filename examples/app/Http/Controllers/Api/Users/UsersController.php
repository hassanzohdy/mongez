<?php
namespace App\Http\Controllers\User;

// these shortcuts will work only if they are in the `alias` list in the `config/organizer.php` file
use Request;
use ApiController; 

class UserController extends ApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'users',
        'listOptions' => [
            'select' => ['users', 'usersGroup'],
        ],
        'rules' => [
            'all' => [
                'name' => 'required|string',
            ],
            'store' => [
                'email' => 'required|string|email|unique:users',
                'password' => 'required|min:8',
                'user_group_id' => 'required|exists:users_group,id',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function updateValidation($id, $request): array 
    {
        return [
            'email' =>  'required|unique:companies,email,' . $id,
            'phone' =>  'required|unique:companies,phone,' . $id,
        ];
    }

    /**
     * Logout user
     *
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $response = [
            'message' => 'Successfully logged out ',
        ];

        return $this->success($response);
    }
}