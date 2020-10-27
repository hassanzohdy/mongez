<?php

namespace App\Modules\Users\Middleware;

use Closure;
use Auth as BaseAuth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Auth
{
    /**
     * Application key
     * 
     * @var string
     */
    protected $apiKey;

    /**
     * Current guardian info
     * 
     * @var array
     */
    protected $currentGuardian;

    /**
     * Current route without the application prefix and the api key
     * 
     * @var string
     */
    protected $currentRoute;

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, Closure $next)
    {
        $this->apiKey = config('app.api-key');

        $uri = $request->uri();

        if (Str::startsWith($uri, '/api')) {
            $uri = Str::replaceFirst('/api', '', $uri);
        }

        $guards = config('auth.guards');

        foreach ($guards as $appType => $guard) {
            if (empty($guard['prefix']) || !Str::startsWith($uri, $guard['prefix'])) continue;

            $this->currentGuardian = $guard;
            $this->currentGuardian['appType'] = $appType;

            $this->currentRoute = Str::replaceFirst($guard['prefix'], '', $uri);

            break;
        }

        config([
            'auth.defaults.guard' => $this->currentGuardian['appType'],
            'app.type' => $this->currentGuardian['appType'],
            'app.users-repo' => $this->currentGuardian['repository'],
            'app.user-type' => $this->currentGuardian['type'],
        ]);

        return $this->middleware($request, $next);
    }

    /**
     * {@inheritDoc}
     */
    protected function middleware(Request $request, Closure $next)
    {
        $ignoredRoutes = $this->currentGuardian['ignoredRoutes'] ?? [];

        if (in_array($this->currentRoute, $ignoredRoutes)) {
            if ($request->authorizationValue() !== $this->apiKey) {
                return response([
                    'error' => 'Invalid Request I',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $next($request);
        } else {
            // validate if and only if the authorization access token is sent
            list($tokenType, $accessToken) = $request->authorization();

            if ($tokenType == 'Bearer') {
                $user = repo($this->currentGuardian['repository'])->getByAccessToken($accessToken);
                if ($user) {
                    BaseAuth::login($user);

                    return $next($request);
                } else {
                    return response([
                        'error' => 'Invalid Request II',
                    ], Response::HTTP_UNAUTHORIZED);
                }
            } else {
                if ($accessToken != $this->apiKey) {
                    return response([
                        'error' => 'Invalid Request III',
                    ], Response::HTTP_UNAUTHORIZED);
                }
                return $next($request);
            }
        }
    }
}
