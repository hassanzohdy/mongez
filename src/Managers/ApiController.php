<?php

namespace HZ\Illuminate\Mongez\Managers;

use Illuminate\Support\MessageBag;
use HZ\Illuminate\Mongez\Events\Events;
use Symfony\Component\HttpFoundation\Response;
use HZ\Illuminate\Mongez\Traits\RepositoryTrait;

abstract class ApiController
{
    use RepositoryTrait;

    /**
     * Return error as array
     * 
     * @const string 
     */
    public const ERROR_AS_ARRAY = 'array';

    /**
     * The returned key in the error array type 
     * 
     * @const string 
     */
    public const ERROR_AS_ARRAY_KEY = 'key';

    /**
     * The returned value in the error array type 
     * 
     * @const string 
     */
    public const ERROR_AS_ARRAY_VALUE = 'value';

    /**
     * Return error as object
     * 
     * @const string 
     */
    public const ERROR_AS_OBJECT = 'object';

    /**
     * Repository name
     * If provided, then the repository property will be the object of the repository
     * 
     * @const string
     */
    public const REPOSITORY_NAME = '';

    /**
     * Repository Object
     * Can be filled when REPOSITORY_NAME is provided.
     * 
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Events Object
     *
     * @var Events
     */
    protected $events;

    /**
     * Constructor
     *
     */
    public function __construct(Events $events)
    {
        $this->events = $events;

        if (static::REPOSITORY_NAME) {
            $this->repository = repo(static::REPOSITORY_NAME);
        }
    }

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

        if (($eventResponse = $this->events->trigger('response.success', $data)) && is_array($eventResponse)) {
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

        if (($eventResponse = $this->events->trigger('response.successCreate', $data)) && is_array($eventResponse)) {
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

        if (($eventResponse = $this->events->trigger('response.badRequest', $data)) && is_array($eventResponse)) {
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
        $errorStrategy = config('mongez.response.error.strategy', self::ERROR_AS_ARRAY);
        $arrayKey = config('mongez.response.error.arrayKey', self::ERROR_AS_ARRAY_KEY);
        $arrayValue = config('mongez.response.error.arrayValue', self::ERROR_AS_ARRAY_VALUE);

        if ($data instanceof MessageBag) {
            $errors = [];

            foreach ($data->messages() as $input => $messagesList) {
                if ($errorStrategy === self::ERROR_AS_OBJECT) {
                    $errors[$input] = $messagesList[0];
                } elseif ($errorStrategy === self::ERROR_AS_ARRAY) {
                    $errors[] = [
                        $arrayKey => $input,
                        $arrayValue => $errorMaxArrayLength === 1 ? $messagesList[0] : array_slice($messagesList, 0, $errorMaxArrayLength),
                    ];
                }
            }

            return ['errors' => $errors];
        } elseif (is_string($data)) {
            if ($errorStrategy === self::ERROR_AS_OBJECT) {
                $data = [
                    'error' => $data,
                ];
            } elseif ($errorStrategy === self::ERROR_AS_ARRAY) {
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

        if (($eventResponse = $this->events->trigger('response.notFound', $data)) && is_array($eventResponse)) {
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

        if (($eventResponse = $this->events->trigger('response.unauthorized', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Send Response
     *
     * @param  array $data
     * @param  int $statusCode
     * @return Response
     */
    protected function send(array $data, int $statusCode)
    {
        if (($eventResponse = $this->events->trigger('response.send', $data, $statusCode)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return response()->json($data, $statusCode);
    }
}
