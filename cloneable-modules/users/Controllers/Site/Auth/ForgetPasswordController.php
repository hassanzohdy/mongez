<?php

namespace App\Modules\Users\Controllers\Site\Auth;

use Mail;
use Validator;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;
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
        $repository = repo(config('app.users-repo'));

        $validator = $this->scan($repository, $request);

        if ($validator->passes()) {
            $user = $repository->getByModel('email', $request->email);

            $user->resetPasswordCode = mt_rand(100000, 999999);
            $user->save();
            Mail::send([], [], function ($message) use ($user) {
                $url = env('APP_URL') . '/reset-password/' . $user->resetPasswordCode;
                $message->to($user->email)
                    ->subject('إستعادة كلمة المرور')
                    // here comes what you want
                    ->setBody("
                    <p>
                        مرحبا بك {$user->name}
                    </p>
                    <p>
                    لقد إستلمت هذا البريد لكي تستعيد كلمة المرور الخاصة بك
                    </p>
                    </p>
                        إذا لم تكن طلبت تغيير كلمة المرور من فضلك تجاهل هذا البريد
                    </p>
                    <p>قم بالضغط على الزر الاتي لإستعادة كلمة المرور</p>
                    <p>
                        <p>كود التفعيل: <strong>{$user->resetPasswordCode}</strong></p>
                        <a href=\"$url\" style=\"display: inline-block; padding: 1rem 3rem; font-weight: bold; color: #FFF; background: #EEA844\">إضغط هنا لإستعادة كلمة المرور</a>
                    </p>
                ", 'text/html'); // assuming text/plain
            });
            return $this->success([
                'resetCode' => $user->resetPasswordCode,
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
    protected function scan(RepositoryInterface $repository, Request $request)
    {
        return Validator::make($request->all(), [
            'email' => 'required|exists:' . $repository->getTableName(),
        ]);
    }
}
