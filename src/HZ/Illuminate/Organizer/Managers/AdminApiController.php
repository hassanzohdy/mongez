<?php
namespace HZ\Illuminate\Organizer\Managers;

use Validator;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

abstract class AdminApiController extends ApiController
{
    /**
     * Repository object
     * 
     * @var mixed
     */
    protected $repository;
    
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
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
     
        if (! empty($this->controllerInfo['repository'])) {
            $this->repository = repo($this->controllerInfo['repository']);
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
    protected function listOptions(Request $request): array
    {
        return $this->controllerInfo('listOptions');
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

        $id = (int) $id;

        if (!$this->repository->has($id)) {
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
     * Get value from controller info
     * 
     * @param  string $key 
     * @return mixed
     */
    protected function controllerInfo(string $key)
    {
        return Arr::get($this->controllerInfo, $key);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array_merge((array) $this->controllerInfo('rules.all'), $this->storeValidation($request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $model = $this->repository->create($request);

        $response = [
            'success' => true,
            'record' => $this->repository->get($model->id),
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
        $this->repository->delete((int) $id);

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
        if (!$this->repository->has($id)) {
            return $this->badRequest('Not Found');
        }

        $rules = array_merge((array) $this->controllerInfo('rules.all'), $this->updateValidation($id, $request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $this->repository->update($id, $request);

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
        return (array) $this->controllerInfo('rules.update');
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
        return (array) $this->controllerInfo('rules.store');
    }
}