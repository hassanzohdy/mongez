<?php

namespace HZ\Illuminate\Mongez\Http;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

abstract class RestfulApiController extends ApiController
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
            'patch' => 'single-record',
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
            'patch' => [],
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
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $json['records'] = $this->repository->list($this->listOptions($request));

        if ($pagination = $this->repository->getPaginationInfo()) {
            $json['paginationInfo'] = $pagination;
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
        $resource = $this->repository->get($id);

        if (!$resource) {
            return $this->notFound();
        }

        return $this->success([
            'record' => $resource,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request = $this->controllerInfo('request.store') ? App::make($this->controllerInfo('request.store')) : $request;

        $rules = array_merge_recursive($this->allValidation($request), $this->storeValidation($request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        if ($errors = $this->beforeStoring($request)) {
            return $this->badRequest($errors);
        }

        if ($errors = $this->beforeAll($request)) {
            return $this->badRequest($errors);
        }

        $model = $this->repository->create($request);

        $returnOnStore = $this->controllerInfo['returnOn']['store'] ?? config('mongez.admin.returnOn.store', 'single-record');

        if ($returnOnStore === 'single-record') {
            return $this->successCreate([
                'record' => $this->repository->wrap($model)
            ]);
        } elseif ($returnOnStore === 'all-records') {
            return $this->index($request);
        } else {
            return $this->successCreate();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = $this->repository->getModel($id);

        if (!$model) {
            return $this->notFound();
        }

        $request = $this->controllerInfo('request.update') ? App::make($this->controllerInfo('request.update')) : $request;

        $rules = array_merge_recursive($this->allValidation($request, $id), $this->updateValidation($id, $request));

        foreach ($rules as &$rulesList) {
            if (!is_array($rulesList)) {
                $rulesList = explode('|', $rulesList);
            }

            foreach ($rulesList as &$rule) {
                if ($rule === 'unique') {
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

        if ($errors = $this->beforeUpdating($model, $request)) {
            return $this->badRequest($errors);
        }

        if ($errors = $this->beforeAll($request)) {
            return $this->badRequest($errors);
        }

        $this->repository->update($model, $request);

        $returnOnUpdate = $this->controllerInfo['returnOn']['update'] ?? config('mongez.admin.returnOn.update', 'single-record');

        if ($returnOnUpdate === 'single-record') {
            return $this->success([
                'record' => $this->repository->wrap($model)
            ]);
        } elseif ($returnOnUpdate === 'all-records') {
            return $this->index($request);
        } else {
            return $this->success();
        }
    }

    /**
     * Edit the specified fields of the specified resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function patch(Request $request, $id)
    {
        $model = $this->repository->getModel($id);

        if (!$model) {
            return $this->notFound();
        }

        $request = $this->controllerInfo('request.patch') ? App::make($this->controllerInfo('request.patch')) : $request;

        $rules = $this->allValidation($request, $id);

        $requestInputs = array_keys($request->all());

        foreach (array_keys($rules) as $column) {
            if (!in_array($column, $requestInputs)) {
                unset($rules[$column]);
            }
        }

        $rules = array_merge_recursive($rules, $this->patchValidation($id, $request));

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->badRequest($validator->errors());
        }

        if ($errors = $this->beforePatching($model, $request)) {
            return $this->badRequest($errors);
        }

        // if ($errors = $this->beforeAll($request)) {
        //     return $this->badRequest($errors);
        // }

        $this->repository->patch($model, $request);

        $returnOnPatch = $this->controllerInfo['returnOn']['patch'] ?? config('mongez.admin.returnOn.patch', 'single-record');

        if ($returnOnPatch === 'single-record') {
            return $this->success([
                'record' => $this->repository->wrap($model)
            ]);
        } elseif ($returnOnPatch === 'all-records') {
            return $this->index($request);
        } else {
            return $this->success();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        // Allow deleting multiple records at once by passing the ids separated by comma
        // i.e DELETE /admin/users/1,2,3
        if (Str::contains($id, ',')) {
            $ids = explode(',', $id);

            $errors = [];

            $totalDeleted = 0;

            foreach ($ids as $id) {
                $model = $this->repository->getModel($id);

                if (!$model) {
                    $errors[] = [
                        'key' => 'notFound',
                        'value' => 'The record with id ' . $id . ' was not found.',
                        'id' => $id
                    ];

                    continue;
                }

                if ($errors = $this->beforeDeleting($model, $request)) {
                    $errors[] = [
                        'key' => 'beforeDeleting',
                        'value' => $errors,
                        'id' => $id
                    ];

                    continue;
                }

                $this->repository->delete($model);

                $totalDeleted++;
            }

            return $errors ? $this->badRequest([
                'errors' => $errors,
                'totalDeleted' => $totalDeleted
            ]) : $this->success([
                'totalDeleted' => $totalDeleted
            ]);
        }

        $model = $this->repository->getModel($id);

        if (!$model) {
            return $this->notFound();
        }

        if ($errors = $this->beforeDeleting($model, $request)) {
            return $this->badRequest($errors);
        }

        $this->repository->delete($model);

        return $this->success();
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
     * @param \Illuminate\Http\Request $request
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
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function updateValidation($id, Request $request): array
    {
        return (array) $this->controllerInfo('rules.update');
    }

    /**
     * Make custom validation for patch
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function patchValidation($id, Request $request): array
    {
        return (array) $this->controllerInfo('rules.patch');
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
     * Triggered before storing a new record
     * Useful when needs to make a validation before storing the record
     * If it returns a value, it will be returned instead
     *
     * @param  Request  $request
     * @return array|null
     */
    protected function beforeStoring(Request $request)
    {
        return null;
    }

    /**
     * Triggered before storing|updating|patching a record
     * Useful when needs to make a validation before operating on a record
     * If it returns a value, it will be returned instead
     *
     * @param  Request  $request
     * @return array|null
     */
    protected function beforeAll(Request $request)
    {
        return null;
    }

    /**
     * Triggered before updating the record
     * Useful when needs to make a validation before updating the record
     * If it returns a value, it will be returned instead
     *
     * @param  Model      $model
     * @param  Request  $request
     * @return array|null
     */
    protected function beforeUpdating($model, Request $request)
    {
        return null;
    }

    /**
     * Triggered before patching the record
     * Useful when needs to make a validation before patching the record
     * If it returns a value, it will be returned instead
     *
     * @param  Model      $model
     * @param  Request  $request
     * @return array|null
     */
    protected function beforePatching($model, Request $request)
    {
        return null;
    }

    /**
     * Triggered before deleting a record
     * Useful when needs to make a validation before deleting the record
     * If it returns a value, it will be returned instead
     *
     * @param  Model      $model
     * @param  Request  $request
     * @return array|null
     */
    protected function beforeDeleting($model, Request $request)
    {
        return null;
    }
}
