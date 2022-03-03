<?php

namespace HZ\Illuminate\Mongez\Http;

use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{

    /**
     * Return error as array
     * 
     * @var string 
     */
    protected string $errorAsArray = 'array';

    /**
     * The returned key in the error array type 
     * 
     * @var string 
     */
    protected string $errorKey = 'key';

    /**
     * The returned value in the error array type 
     * 
     * @var string 
     */
    protected string $errorValue = 'value';

    /**
     * Return error as object
     * 
     * @var string 
     */
    protected string $errorStrategy = 'object';

    /**
     * Send success data
     *
     * @param array $data
     * @return string
     */
    protected function success($data = null)
    {
        $data = $data ?: config('mongez.response.defaults.success', [
            'success' => true,
        ]);

        if (($eventResponse = events()->trigger('response.success', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_OK);
    }

    /**
     * Send Success success data
     *
     * @param array $data
     * @return string
     */
    protected function successCreate($data = null)
    {
        $data = $data ?: config('mongez.response.defaults.successCreate', [
            'success' => true,
        ]);

        if (($eventResponse = events()->trigger('response.successCreate', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_CREATED);
    }

    /**
     * Send bad request data
     *
     * @param  array $data
     * @return string
     */
    protected function badRequest($data)
    {
        $data = $this->mapResponseError($data);

        if (($eventResponse = events()->trigger('response.badRequest', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Map error based on configurations
     * 
     * @param  mixed $data
     * @return array
     */
    protected function mapResponseError($data)
    {
        $errorMaxArrayLength = config('mongez.response.error.maxArrayLength', 1);
        $errorStrategy = config('mongez.response.error.strategy', $this->errorAsArray);
        $arrayKey = config('mongez.response.error.key', $this->errorKey);
        $arrayValue = config('mongez.response.error.value', $this->errorValue);

        if ($data instanceof MessageBag) {
            $errors = [];

            foreach ($data->messages() as $input => $messagesList) {
                if ($errorStrategy === $this->errorStrategy) {
                    $errors[$input] = $messagesList[0];
                } elseif ($errorStrategy === $this->errorAsArray) {
                    $errors[] = [
                        $arrayKey => $input,
                        $arrayValue => $errorMaxArrayLength === 1 ? $messagesList[0] : array_slice($messagesList, 0, $errorMaxArrayLength),
                    ];
                }
            }

            return ['errors' => $errors];
        } elseif (is_string($data)) {
            if ($errorStrategy === $this->errorStrategy) {
                $data = [
                    'error' => $data,
                ];
            } elseif ($errorStrategy === $this->errorAsArray) {
                $data = [
                    [
                        $arrayKey => 'error',
                        $arrayValue => $data,
                    ]
                ];
            }

            return ['errors' => $data];
        }

        return $data;
    }

    /**
     * Send not found request data
     *
     * @param  string $data
     * @return Response
     */
    protected function notFound($data = null)
    {
        if ($data === null) {
            $data = config('mongez.response.defaults.notFound', trans('response.notFound'));
        }

        $data = $this->mapResponseError($data);

        if (($eventResponse = events()->trigger('response.notFound', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized data
     *
     * @param  mixed $data
     * @return string
     */
    protected function unauthorized($data = null)
    {
        $data = $this->mapResponseError($data ?: config('mongez.response.defaults.unauthorized', trans('response.unauthorized')));

        if (($eventResponse = events()->trigger('response.unauthorized', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Send Response
     *
     * @param  array $data
     * @param  int $statusCode
     * @param  array $headers
     * @param  mixed $jsonOptions
     * @return Response
     */
    protected function send(array $data, int $statusCode, array $headers = [], $jsonOptions = JSON_PRESERVE_ZERO_FRACTION)
    {
        if (($eventResponse = events()->trigger('response.send', $data, $statusCode, $headers, $jsonOptions)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return response()->json(
            $data,
            $statusCode,
            $headers,
            $jsonOptions
        );
    }
}
