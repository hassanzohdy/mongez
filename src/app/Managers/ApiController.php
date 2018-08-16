<?php
namespace App\Managers;

use Arr;
use App;
use Auth;
use Request;
use Validator;
use Illuminate\Http\Response;
use App\Traits\RepositoryTrait;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    use RepositoryTrait;

    /**
     * Controller repository
     * 
     * @var mixed
     */
    protected $controllerInfo = [
        'repository' => '',
        'records' => [
            'select' => [],
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];

    /**
     * User Model
     * 
     * @var mixed
     */
    protected $user = true;

    /**
     * Repository
     * 
     * @var \App\Managers\RepositoryManager
     */
    protected $repository;

    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        if ($this->controllerInfo['repository']) {
            $this->repository = repo($this->controllerInfo['repository']);
        }

        // if the user property is set to true
        // then we will inject the user property to the controller
        // so it will be used easily
        if ($this->user === true) {
            $this->user = user();
        }
    }

    /**
     * Get List of records
     * 
     * @param  \Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $json['records'] = $this->repository->list($this->listOptions($request));

        return $this->success($json);    
    }
    
    /**
     * Get  options
     * 
     * @param \Request $request
     * @return array
     */
    protected function listOptions(Request $request) :array
    {
        return $this->controllerInfo['listOptions'];
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $access = true;

        if (! $this->repository->has($id)) {
            return $this->badRequest('not-found');
        }

        if ($request->check_permission) {
            $key = $request->check_permission;
            $access = $this->user->canAccessKey($key);
        }

        $json['record'] = $this->repository->get($id);

        return $access ? $this->success($json) : 
                         $this->unauthorized('You Do not Have permission');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $rules = array_merge((array) $this->controllerInfo['rules']['all'], $this->storeValidation($request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $model =  $this->repository->create($request);

        $response = [
            'success' => true,
            'record'  =>$this->repository->get($model->id)
        ];
        return $this->success($response);
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {        
        $this->repository->delete($id);
        
        $response = [
            'success' => true,
        ];

        return $this->success($response);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! $this->repository->has($id)) {
            return $this->badRequest('Not Found');
        }
        
        $rules = array_merge((array) $this->controllerInfo['rules']['all'], $this->updateValidation($id, $request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $this->repository->update($request, $id);

        $response = [
            'success' => true,
        ];

        return $this->success($response);
    }

    /**
     * Make custom validation for update
     * 
     * @param  int $id
     * @param  \Request $request
     * @return array
     */
    protected function updateValidation($id, Request $request): array
    {
        return (array) $this->controllerInfo['rules']['update'];
    }

    /**
     * Make custom validation for store
     * 
     * @param int $id
     * @param mixed $request 
     * @return array
     */
    protected function storeValidation($request): array
    {
        return (array) $this->controllerInfo['rules']['store'];
    }

    /**
     * Send success data
     *
     * @param array $data
     * @return string
     */
    protected function success($data)
    {
        if ($data instanceof MessageBag) {
            $data = ['data' => $data];
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
            $data = ['errors' => $data->messages()];
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
        $message = ['error'=> $message];

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