<?php

namespace HZ\Illuminate\Mongez\Macros\Http;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Request
{
    /**
     * If the authorization argument is auto, then it will be auto detect the 
     * value of the passed Authorization header
     * 
     * @const bool
     */
    const AUTO = true;

    /**
     * Get request referer
     * 
     * @return string
     */
    public function referer()
    {
        return function () {
            return $this->server('HTTP_REFERER');
        };
    }

    /**
     * Get request uri
     * 
     * @return string
     */
    public function uri()
    {
        return function () {
            $script = str_replace('/index.php', '', $this->server('SCRIPT_NAME'));

            return '/' . ltrim(Str::removeFirst($script, $this->server('REQUEST_URI')), '/');
        };
    }

    /**
     * Get the value of the Authorization header
     * 
     * @return array
     */
    public function authorization()
    {
        return function (): array {
            $authorization = $this->server('HTTP_AUTHORIZATION') ?: $this->server('REDIRECT_HTTP_AUTHORIZATION');

            if (!$authorization) {
                if ($token = $this->get('Token')) {
                    return ['Bearer', $token];
                } elseif ($key = $this->get('Key')) {
                    return ['key', $key];
                }

                return [];
            }

            return explode(' ', $authorization);
        };
    }

    /**
     * Add files to request
     * 
     * @param string $fileName
     * @param UploadedFile $file
     * 
     * @return void
     */
    public function addFile()
    {
        return function (string $fileName, UploadedFile $file) {
            $this->convertedFiles[$fileName] = $file;
        };
    }

    /**
     * Get authorization value only
     * If the authorization argument is auto, then it will be auto detect the 
     * value of the passed Authorization header
     * 
     * If the passed argument is set false, then the whole value will be returned
     * 
     * @param  string|bool $authorizationType
     * @return string|null
     */
    public function authorizationValue()
    {
        return function ($authorizationType = Request::AUTO) {
            $authorization = $this->authorization();

            if (!$authorization) return null;

            list($type, $value) = $authorization;

            if ($authorizationType === Request::AUTO) {
                return $value;
            }

            if ($authorizationType === $type) return $value;
        };
    }
}
