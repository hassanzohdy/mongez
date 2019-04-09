<?php
namespace App\ModuleName\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Organizer\Managers\ApiController;

class ControllerName extends ApiController
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