<?php
namespace HZ\Laravel\Organizer\App\Managers;

use App;
use Arr;
use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use HZ\Laravel\Organizer\App\Traits\RepositoryTrait;

abstract class ApiController extends Controller
{
    use RepositoryTrait;

    /**
     * User Model
     *
     * @var mixed
     */
    protected $user = true;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        // if the user property is set to true
        // then we will inject the user property to the controller
        // so it will be used easily
        if ($this->user === true) {
            $this->user = user();
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

        return $this->send(Response::HTTP_BAD_REQUEST, $data);
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
        return response()->json($message, $statusCode);
    }
}
