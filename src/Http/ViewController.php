<?php

namespace HZ\Illuminate\Mongez\Http;

use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Repository\RepositoryInterface;
use HZ\Illuminate\Mongez\Repository\Concerns\RepositoryTrait;

abstract class ViewController extends Controller
{
    use RepositoryTrait;

    /**
     * Repository name
     * If provided, then the repository property will be the object of the repository
     * 
     * @const string
     */
    protected const REPOSITORY_NAME = '';

    /**
     * The module registered views path 
     * 
     * @const string
     */
    protected const VIEW_PATH = '';

    /**
     * Repository OBject
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
        }
    }

    /**
     * Render the given view path
     * 
     * @param string $viewPath
     * @param  array $data
     * @return string
     */
    protected function render(string $viewPath, array $data = [])
    {
        return view(static::VIEW_PATH . '.' . $viewPath, $data);
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

        return $this->send(Response::HTTP_OK, $data);
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

            foreach ($data->messages() as $input => $messagesList) {
                $errors[$input] = $messagesList[0];
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

        return $this->send(Response::HTTP_BAD_REQUEST, $data);
    }

    /**
     * Send not found request data
     *
     * @param  array $data
     * @return string
     */
    protected function notFound($data)
    {
        if ($data instanceof MessageBag) {
            $errors = [];

            foreach ($data->messages() as $input => $messagesList) {
                $errors[$input] = $messagesList[0];
            }

            $data = ['errors' => $errors];
        } elseif (is_string($data)) {
            $data = [
                'error' => $data,
            ];
        }

        if (($eventResponse = $this->events->trigger('response.notFound', $data)) && is_array($eventResponse)) {
            $data = $eventResponse;
        }

        return $this->send(Response::HTTP_NOT_FOUND, $data);
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

        return $this->send(Response::HTTP_UNPROCESSABLE_ENTITY, $message);
    }

    /**
     * Send Response
     *
     * @param  int $statusCode
     * @param  array $message
     * @return string
     */
    protected function send(int $statusCode, array $message)
    {
        if (($eventResponse = $this->events->trigger('response.send', $message, $statusCode)) && is_array($eventResponse)) {
            $message = $eventResponse;
        }

        return response()->json($message, $statusCode);
    }
}
