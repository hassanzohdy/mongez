<?php
namespace App\Http\Controllers\Api\Site\ControllerPath;

use Validator;
use Illuminate\Http\Request;

class SetterController extends \ApiController
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
        $validator = $this->scan($request);

        if ($validator->passes()) {
            $this->repository->create($request);

            return $this->success([
                'success' => true,
            ]);
        } else {
            return $this->badRequest($validator->errors());
        }
    }
    
    /**
     * Determine whether the passed values are valid
     *
     * @return mixed
     */
    private function scan(Request $request)
    {
        return Validator::make($request->all(), [
            // rules list
        ]);
    }
}