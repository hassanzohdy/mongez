<?php
namespace App\Modules\ModuleName\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\BaseController;

class ControllerName extends BaseController
{
    /**
     * Repository name
     * 
     * @var string
     */
    protected const REPOSITORY_NAME = 'repo-name';

    VIEW

    /**
     * {@inheritDoc}
     */
    public function index(Request $request)
    {
        $options = [];

        return $this->success([
            'records' => $this->repository->list($options),
        ]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function show($id, Request $request)
    {
        return $this->success([
            'record' => $this->repository->get($id),
        ]);
    }
}