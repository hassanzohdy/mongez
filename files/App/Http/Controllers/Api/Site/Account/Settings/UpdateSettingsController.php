<?php
namespace App\Http\Controllers\Api\Site\Account\Settings;

use Request;
use Validator;
use ApiController;
use Illuminate\Validation\Rule;

class UpdateSettingsController extends ApiController
{
    /**
     * Create new users
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $validator = $this->scan($request);

        $usersRepository = $this->{config('app.user-repo')};

        if ($validator->passes()) {
            $customer = $usersRepository->update(user()->id, $request);

            return $this->success([
                'user' => $usersRepository->wrap($customer),
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
            'name' => 'required',
            'email' => [
                'required',
                Rule::unique(config('app.user-type'))->ignore(user()->id),
            ],
        ]);
    }
}