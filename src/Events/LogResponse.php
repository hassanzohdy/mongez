<?php

namespace HZ\Illuminate\Mongez\Events;

use HZ\Illuminate\Mongez\Models\ResponseLog;
use HZ\Illuminate\Mongez\Contracts\Users\UserAccountTypeContract;

class LogResponse
{
    /**
     * {@inheritDoc}
     */
    public function log($response, $statusCode)
    {
        $request = request();

        $userInfo = null;

        if ($user = user()) {
            $userInfo = $user->sharedInfo();
            if ($user instanceof UserAccountTypeContract) {
                $userInfo['accountType'] = $user->getAccountType();
            }
        }

        $response = json_decode(response($response)->getContent(), true);

        ResponseLog::create([
            'response' => $response,
            'statusCode' => $statusCode,
            'request' => $request->all(),
            'userAgent' => $request->userAgent(),
            'route' => $request->uri(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'user' => $userInfo,
        ]);

        return $response;
    }
}
