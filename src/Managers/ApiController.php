<?php

namespace HZ\Illuminate\Mongez\Managers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\App;
use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Traits\RepositoryTrait;

abstract class ApiController extends Controller
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
    protected const REPOSITORY_NAME = '';

    /**
     * Repository Object
     * Can be filled when REPOSITORY_NAME is provided.
     * 
     * @var RepositoryInterface
     */
    protected $repository = null;

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
    public function __construct()
    {
        $this->events = App::make(Events::class);

        if (static::REPOSITORY_NAME) {
            $this->repository = repo(static::REPOSITORY_NAME);
        } elseif ($this->repository) {
            $this->repository = repo($this->repository);
        }
    }

    /**
     * Send success data
     *
     * @param array $data
     * @return string
     */
    protected function success($data = [])
    {
        $data = $data ?: [
            'success' => true,
        ];

        if (($eventResponse = $this->events->trigger('response.success', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_OK);
    }

    /**
     * Send bad request data
     *
     * @param  array $data
     * @return string
     */
    protected function badRequest($data)
    {
        if ($data instanceof MessageBag) {
            $errors = [];

            $errorReturn = config('mongez.controllers.response.errors.strategy', self::ERROR_AS_ARRAY);
            $arrayKey = config('mongez.controllers.errors.arrayKey', self::ERROR_AS_ARRAY_KEY);
            $arrayValue = config('mongez.controllers.errors.ArrayValue', self::ERROR_AS_ARRAY_VALUE);

            foreach ($data->messages() as $input => $messagesList) {
                if ($errorReturn === self::ERROR_AS_OBJECT) {
                    $errors[$input] = $messagesList[0];
                } elseif ($errorReturn === self::ERROR_AS_ARRAY) {
                    $errors[] = [
                        $arrayKey => $input,
                        $arrayValue => $messagesList,
                    ];
                }
            }

            $data = ['errors' => $errors];
        } elseif (is_string($data)) {
            $data = [
                'error' => $data,
            ];
        }

        if (($eventResponse = $this->events->trigger('response.badRequest', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Send not found request data
     *
     * @param  array $data
     * @return string
     */
    protected function notFound($data = [])
    {
        if (is_string($data)) {
            $data = [
                'error' => $data,
            ];
        }

        if (($eventResponse = $this->events->trigger('response.notFound', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send($data, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized data
     *
     * @param  string $message
     * @return string
     */
    protected function unauthorized(string $message)
    {
        $message = ['error' => $message];

        if (($eventResponse = $this->events->trigger('response.unauthorized', $message)) && is_array($eventResponse)) {
            $message = $eventResponse;
        }

        return $this->send($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Send Response
     *
     * @param  array $message
     * @param  int $statusCode
     * @return Response
     */
    protected function send(array $message, int $statusCode)
    {
        if (($eventResponse = $this->events->trigger('response.send', $message, $statusCode)) && is_array($eventResponse)) {
            $message = $eventResponse;
        }

        return response()->json($message, $statusCode);
    }
}
