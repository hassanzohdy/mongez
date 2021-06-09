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
        $errorMaxArrayLength = config('mongez.response.errors.maxArrayLength', 1);
        $errorReturn = config('mongez.response.errors.strategy', self::ERROR_AS_ARRAY);
        $arrayKey = config('mongez.response.errors.arrayKey', self::ERROR_AS_ARRAY_KEY);
        $arrayValue = config('mongez.response.errors.arrayValue', self::ERROR_AS_ARRAY_VALUE);

        if ($data instanceof MessageBag) {
            $errors = [];

            foreach ($data->messages() as $input => $messagesList) {
                if ($errorReturn === self::ERROR_AS_OBJECT) {
                    $errors[$input] = $messagesList[0];
                } elseif ($errorReturn === self::ERROR_AS_ARRAY) {
                    $errors[] = [
                        $arrayKey => $input,
                        $arrayValue => $errorMaxArrayLength === 1 ? $messagesList[0] : array_slice($messagesList, 0, $errorMaxArrayLength),
                    ];
                }
            }

            $data = ['errors' => $errors];
        } elseif (is_string($data)) {
            $data = [
                'errors' => [
                    [
                        $arrayKey => 'error',
                        $arrayValue => $data,
                    ]
                ],
            ];
        }

        return $data;
    }

    /**
     * Send not found request data
     *
     * @param  array $data
     * @return string
     */
    protected function notFound($data)
    {
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
    protected function unauthorized($data)
    {
        $data = $this->mapResponseError($data);

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
