<?php

namespace HZ\Illuminate\Mongez\Managers;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

abstract class AdminApiController extends ApiController
{
    /**
     * Controller repository
     *
     * @var mixed
     */
    protected $controllerInfo = [
        'repository' => '',
        'listOptions' => [
            'select' => [],
            'paginate' => null
        ],
        'returnOn' => [
            'store' => 'single-record',
            'update' => 'single-record',
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

        if (!empty($this->controllerInfo['repository'])) {
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

        if ($this->repository->getPaginateInfo()) {
            $json['paginationInfo'] = $this->repository->getPaginateInfo();
        }

        return $this->success($json);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $id = (int) $id;

        if (!$this->repository->has($id)) {
            return $this->notFound();
        }

        return $this->success([
            'record' => $this->repository->get($id),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array_merge($this->allValidation($request), $this->storeValidation($request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $model = $this->repository->create($request);

        $returnOnStore = $this->controllerInfo['returnOn']['store'] ?? config('mongez.admin.returnOn.store', 'single-record');

        if ($returnOnStore == 'single-record') {
            return $this->show($model->id, $request);
        } elseif ($returnOnStore == 'all-records') {
            return $this->index($request);
        } else {
            return $this->success();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!$this->repository->has($id)) {
            return $this->notFound();
        }

        if ($errors = $this->beforeDeleting($id, $request)) {
            return $this->badRequest($errors);
        }

        $this->repository->delete((int) $id);

        return $this->success();
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
            return $this->notFound();
        }

        $rules = array_merge($this->allValidation($request, $id), $this->updateValidation($id, $request));

        foreach ($rules as &$rulesList) {
            if (!is_array($rulesList)) {
                $rulesList = explode('|', $rulesList);
            }

            foreach ($rulesList as &$rule) {
                if ($rule == 'unique') {
                    if (!Str::contains($rule, ':')) {
                        $rule = Rule::unique($this->repository->getTableName())->ignore((int) $id, 'id');
                    }
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $this->repository->update($id, $request);

        $returnOnUpdate = $this->controllerInfo['returnOn']['update'] ?? config('mongez.admin.returnOn.update', 'single-record');

        if ($returnOnUpdate == 'single-record') {
            return $this->show($id, $request);
        } elseif ($returnOnUpdate == 'all-records') {
            return $this->index($request);
        } else {
            return $this->success();
        }
    }

    /**
     * Edit the specified fields of the specified resource
     * 
     * @param  \Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function patch(Request $request, $id)
    {
        if (!$this->repository->has($id)) {
            return $this->notFound();
        }

        $rules = array_merge($this->allValidation($request, $id), $this->updateValidation($id, $request));

        foreach ($rules as $column => $rulesList) {
            if (!in_array($column, array_keys($request->all()))) {
                unset($rules[$column]);
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        $this->repository->patch($id, $request);

        $returnOnUpdate = $this->controllerInfo['returnOn']['update'] ?? config('mongez.admin.returnOn.update', 'single-record');

        if ($returnOnUpdate == 'single-record') {
            return $this->show($id, $request);
        } elseif ($returnOnUpdate == 'all-records') {
            return $this->index($request);
        } else {
            return $this->success();
        }
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
     * Get  options
     *
     * @param \Request $request
     * @return array
     */
    protected function listOptions(Request $request): array
    {
        $requestData = $request->all();

        if ($request->sortBy) {
            $requestData['orderBy'] = [$request->sortBy, $request->sortDirection];
        };

        return array_merge($requestData, (array) $this->controllerInfo('listOptions'));
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
     * @param mixed $request
     * @return array
     */
    protected function storeValidation($request): array
    {
        return (array) $this->controllerInfo('rules.store');
    }

    /**
     * Make custom validation for store or update
     *
     * @param mixed $request
     * @param int $id
     * @return array
     */
    protected function allValidation($request, $id = null): array
    {
        return (array) $this->controllerInfo('rules.all');
    }

    /**
     * Triggered before deleting a record
     * Useful when needs to make a validation before deleting the record
     * If it returns a value, it will be returned instead
     *
     * @param  int      $model
     * @param  Request  $request
     * @return array|null
     */
    protected function beforeDeleting($id, Request $request)
    {
        return null;
    }
}
