<?php

namespace App\Modules\Users\Controllers\Admin;

use Illuminate\Validation\Rule;
use use HZ\Illuminate\Mongez\Http\RestfulApiController;;

class UsersController extends RestfulApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'users',
        'listOptions' => [
            'select' => ['id', 'name', 'group', 'email'],
            'filterBy' => [],
            'paginate' => null, // if set null, it will be automated based on repository configuration option
        ],
        'rules' => [
            'all' => [
                // 'name' => 'required|string',
            ],
            'store' => [],
            'update' => [],
        ],
    ];

    /**
     * Make custom validation for store.
     *
     * @param mixed $request
     *
     * @return array
     */
    protected function storeValidation($request): array
    {
        return [
            'email' => [
                'required',
                Rule::unique('users', 'email')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'password' => 'required',
        ];
    }

    /**
     * Make custom validation for store.
     *
     * @param int $id
     * @param mixed $request
     * @return array
     */
    protected function updateValidation($id, $request): array
    {
        return [
            'email' => [
                'required',
                Rule::unique('users', 'email')->where(function ($query) use ($id) {
                    $query->whereNull('deleted_at');
                    $query->where('id', '!=', (int) $id);
                }),
            ],
        ];
    }
}
