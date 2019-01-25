<?php
namespace App\Http\Controllers\Api\Site\ControllerPath;

use Illuminate\Http\Request;

class GetterController extends \ApiController
{
    /**
     * Repository name
     * 
     * @var string
     */
    protected $repository = 'repo-name';

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
}